<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $this->authorize('orders view');

        $query = Invoice::with(['order.customer']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas(
                        'order',
                        fn($oq) =>
                        $oq->where('order_number', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        return view('central.invoices.index', [
            'invoices' => $query->latest()->paginate(10)->withQueryString()
        ]);
    }

    /**
     * Generate a new invoice and redirect to PREVIEW.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            return DB::transaction(function () use ($validated) {

                $order = Order::lockForUpdate()->findOrFail($validated['order_id']);

                if ($order->invoices()->exists()) {
                    throw new Exception("Invoice already exists for order {$order->order_number}");
                }

                $invoice = Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                    'issue_date' => now(),
                    'due_date' => $validated['due_date'] ?? now()->addDays(30),
                    'total_amount' => $order->grand_total ?? $order->total_amount,
                    'paid_amount' => 0,
                    'status' => 'sent',
                ]);

                // 👉 Redirect to PREVIEW
                return redirect()
                    ->route('central.invoices.show', $invoice)
                    ->with('success', 'Invoice generated successfully.');
            });
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Invoice PREVIEW (HTML)
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('orders view');

        $invoice->load([
            'order.items',
            'payments',
            'order.customer',
            'order.billingAddress',
            'order.shippingAddress',
            'order.warehouse',
        ]);

        return view('central.invoices.show', compact('invoice'));
    }

    /**
     * Download OR Print Invoice PDF
     * ?action=print → stream
     */
    public function pdf(Invoice $invoice, Request $request)
    {
        $this->authorize('orders view');

        $invoice->load([
            'order.items',
            'payments',
            'order.customer',
            'order.billingAddress',
            'order.shippingAddress',
            'order.warehouse',
        ]);

        $pdf = Pdf::loadView('central.invoices.print', compact('invoice'));

        if ($request->query('action') === 'print') {
            return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
        }

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Record a payment against the invoice.
     */
    public function addPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('orders manage');

        $remaining = round($invoice->total_amount - $invoice->paid_amount, 2);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $remaining],
            'method' => 'required|string|in:cash,bank_transfer,online,cheque',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $invoice) {

                $invoice = Invoice::lockForUpdate()->find($invoice->id);

                Payment::create([
                    'invoice_id' => $invoice->id,
                    'order_id' => $invoice->order_id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'paid_at' => now(),
                ]);

                $invoice->increment('paid_amount', (float) $validated['amount']);
                $invoice->refresh();

                $status = round((float) $invoice->paid_amount, 2) >= round((float) $invoice->total_amount, 2)
                    ? 'paid'
                    : 'partial';

                $invoice->update(['status' => $status]);
                $invoice->order->update(['payment_status' => $status]);
            });

            return back()->with('success', 'Payment recorded successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


}
