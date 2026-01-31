<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['order.customer']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
             $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 10);
        $invoices = $query->latest()->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('central.invoices.index', compact('invoices'))->render();
        }

        return view('central.invoices.index', compact('invoices'));
    }

    // Generate Invoice from Order
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->invoices()->exists()) {
             // For simplicity, allowing multiple invoices per order if partial, but let's stick to one for now
             // return back()->with('error', 'Invoice already exists for this order.');
        }

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(8)), // Or sequential logic
            'issue_date' => now(),
            'due_date' => $request->due_date ?? now()->addDays(30),
            'total_amount' => $order->total_amount, // or grand_total if available
            'paid_amount' => 0,
            'status' => 'sent', // default to sent
        ]);

        return redirect()->route('central.invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.items', 'payments', 'order.customer']);
        return view('central.invoices.show', compact('invoice'));
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id,
                'amount' => $request->amount,
                'method' => $request->method,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'paid_at' => now(),
            ]);

            $invoice->increment('paid_amount', $request->amount);
            
            // Update Status
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
                $invoice->order->update(['payment_status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partial']);
                $invoice->order->update(['payment_status' => 'partial']);
            }
        });

        return back()->with('success', 'Payment recorded.');
    }
}
