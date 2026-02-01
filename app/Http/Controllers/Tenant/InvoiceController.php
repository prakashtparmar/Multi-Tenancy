<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the invoices.
     */
    public function index(): View
    {
        $this->authorize('orders view');
        
        $invoices = Invoice::with(['order.customer'])->latest()->paginate(10);
        return view('tenant.invoices.index', compact('invoices'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $order = Order::findOrFail($request->order_id);

        try {
            $invoice = DB::transaction(function () use ($order, $request) {
                return Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                    'issue_date' => now(),
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'total_amount' => $order->total_amount,
                    'paid_amount' => 0,
                    'status' => 'sent',
                ]);
            });

            return redirect()->route('tenant.invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate invoice.');
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('orders view');
        
        $invoice->load(['order.items', 'payments', 'order.customer']);
        return view('tenant.invoices.show', compact('invoice'));
    }

    /**
     * Add a payment to the invoice.
     */
    public function addPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $invoice) {
                Payment::create([
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
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record payment.');
        }
    }
}
