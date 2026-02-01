<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of central invoices.
     */
    public function index(Request $request): View
    {
        $this->authorize('orders view');

        $query = Invoice::with(['order.customer']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
             $query->where('status', (string) $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $invoices = $query->latest()->paginate($perPage)->withQueryString();

        return view('central.invoices.index', compact('invoices'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            /** @var Order $order */
            $order = Order::findOrFail($validated['order_id']);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'issue_date' => now(),
                'due_date' => $validated['due_date'] ?? now()->addDays(30),
                'total_amount' => $order->total_amount,
                'paid_amount' => 0,
                'status' => 'sent',
            ]);

            return redirect()->route('central.invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('orders view');
        $invoice->load(['order.items', 'payments', 'order.customer']);
        return view('central.invoices.show', compact('invoice'));
    }

    /**
     * Add a payment to the invoice.
     */
    public function addPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('orders manage');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $invoice) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'order_id' => $invoice->order_id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'paid_at' => now(),
                ]);

                $invoice->increment('paid_amount', $validated['amount']);
                
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
        } catch (Exception $e) {
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }
}
