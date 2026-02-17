<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderProcessingController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * List orders that need processing (Confirmed, Processing, Ready to Ship)
     */
    /**
     * List orders with filtering capabilities
     */
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'items', 'warehouse', 'shipments', 'invoices'])
            ->latest();

        // Default: Show active processing orders if no status is selected
        if (!$request->has('status')) {
            $query->whereIn('status', ['confirmed', 'processing', 'ready_to_ship']);
        }
        // If status is provided
        elseif ($request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }
        // If status is 'all', we show everything (already defaults to latest so no extra filter needed)

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->search($search);
                    });
            });
        }

        // Date Filtering
        if ($request->filled('date_filter')) {
            $dateFilter = $request->input('date_filter');
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', now());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', now()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->input('start_date'));
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->input('end_date'));
                    }
                    break;
            }
        }

        $orders = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('tenant.processing.orders.partials.orders-list', compact('orders'));
        }

        return view('tenant.processing.orders.index', compact('orders'));
    }

    /**
     * Mark order as Processing (Confirmed -> Processing)
     */
    public function process(Order $order): RedirectResponse|JsonResponse
    {
        try {
            if ($order->status !== 'confirmed') {
                throw new Exception('Order must be Confirmed before Processing.');
            }

            $order->update([
                'status' => 'processing',
                'shipping_status' => 'pending',
                'updated_by' => auth()->id(),
            ]);

            // Eager load relationships for the modal
            $order->refresh()->load(['customer', 'items.product', 'shippingAddress']);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order marked as Processing.',
                    'order' => $order,
                ]);
            }

            return redirect()->route('tenant.processing.orders.index')
                ->with('success', 'Order marked as Processing.')
                ->with('processed_order', $order);
        } catch (Exception $e) {
            return back()->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Mark order as Ready to Ship (Processing -> Ready to Ship)
     * Also generates invoice.
     */
    public function readyToShip(Order $order): RedirectResponse
    {
        try {
            DB::transaction(function () use ($order) {
                if ($order->status !== 'processing') {
                    throw new Exception('Order must be Processing before Ready to Ship.');
                }

                $order->update([
                    'status' => 'ready_to_ship',
                    'shipping_status' => 'pending',
                    'updated_by' => auth()->id(),
                ]);

                // Ensure Invoice Exists
                if ($order->invoices()->doesntExist()) {
                    Invoice::create([
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                        'issue_date' => now(),
                        'due_date' => now(),
                        'total_amount' => $order->grand_total,
                        'paid_amount' => 0, // Assuming COD or paid later, or update if prepaid
                        'status' => 'unpaid',
                    ]);
                }
            });

            return back()->with('success', 'Order marked as Ready to Parcel. Invoice generated.');
        } catch (Exception $e) {
            return back()->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Bulk Dispatch via CSV
     */
    public function bulkDispatch(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // Get header row

        // Normalize header keys to lowercase
        $header = array_map('strtolower', $header);

        // Required columns
        $required = ['order_number', 'courier', 'tracking_number'];
        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            fclose($handle);
            return back()->with('error', 'CSV is missing required columns: ' . implode(', ', $missing));
        }

        // Map header indices
        $indices = array_flip($header);

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            try {
                $orderNumber = $row[$indices['order_number']] ?? null;
                $courier = $row[$indices['courier']] ?? null;
                $tracking = $row[$indices['tracking_number']] ?? null;

                if (!$orderNumber || !$courier || !$tracking) {
                    $failCount++;
                    continue;
                }

                $order = Order::where('order_number', $orderNumber)->first();

                if (!$order) {
                    $failCount++;
                    $errors[] = "Order $orderNumber not found.";
                    continue;
                }

                // Only dispatch if ready to ship or processing (flexible)
                if (!in_array($order->status, ['ready_to_ship', 'processing'])) {
                    $failCount++;
                    $errors[] = "Order $orderNumber is in status '{$order->status}', cannot dispatch.";
                    continue;
                }

                $this->orderService->shipOrder($order, $tracking, $courier);
                $successCount++;

            } catch (Exception $e) {
                $failCount++;
                $errors[] = "Error dispatching $orderNumber: " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "Bulk Dispatch Completed: $successCount successful, $failCount failed.";

        if ($failCount > 0) {
            // Include first few errors in message if any
            $errorMsg = implode(' | ', array_slice($errors, 0, 3));
            if (count($errors) > 3)
                $errorMsg .= '...';
            return back()->with('warning', "$message Errors: $errorMsg");
        }

        return back()->with('success', $message);
    }

    /**
     * Dispatch Order (Ready to Ship -> Shipped)
     * Requires courier details.
     */
    public function dispatch(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'courier' => 'required|string|max:255',
            'tracking_number' => 'required|string|max:255',
        ]);

        try {
            // Use existing service to handle stock deduction and shipment creation
            $this->orderService->shipOrder(
                $order,
                $validated['tracking_number'],
                $validated['courier']
            );

            return back()->with('success', 'Order Dispatched (Shipped) successfully.');

        } catch (Exception $e) {
            return back()->with('error', 'Error dispatching order: ' . $e->getMessage());
        }
    }

    /**
     * List approved returns that need to be received
     */
    public function indexReturns(Request $request): View
    {
        $query = OrderReturn::with(['order.customer', 'items.product'])
            ->whereIn('status', ['approved', 'received']);

        // Stats Calculation (Before filtering)
        $stats = [
            'total' => OrderReturn::whereIn('status', ['approved', 'received'])->count(),
            'approved' => OrderReturn::where('status', 'approved')->count(),
            'received' => OrderReturn::where('status', 'received')->count(),
        ];

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $returns = $query->latest()->paginate(10)->withQueryString();

        return view('tenant.processing.returns.index', compact('returns', 'stats'));
    }

    /**
     * Receive Returned Items
     * Updates return status to 'received' and increments inventory.
     */
    public function receiveReturn(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:return_items,id',
            'items.*.condition' => 'required|in:sellable,damaged',
            'items.*.verified' => 'required|accepted',
        ]);

        try {
            if ($orderReturn->status !== 'approved') {
                throw new Exception("Return must be Approved before receiving items.");
            }

            DB::transaction(function () use ($orderReturn, $validated) {
                // Update Return Status
                $orderReturn->update(['status' => 'received']);

                foreach ($validated['items'] as $inspectedItem) {
                    $item = $orderReturn->items()->findOrFail($inspectedItem['item_id']);

                    // Update item condition based on inspection
                    $item->update(['condition' => $inspectedItem['condition']]);

                    if ($inspectedItem['condition'] === 'sellable') {
                        $warehouseId = $orderReturn->order->warehouse_id;

                        // Find or Create Stock record
                        $stock = InventoryStock::firstOrCreate(
                            ['warehouse_id' => $warehouseId, 'product_id' => $item->product_id],
                            ['quantity' => 0, 'reserve_quantity' => 0]
                        );

                        // Increment Quantity
                        $stock->increment('quantity', (int) $item->quantity);

                        // Log Movement
                        InventoryMovement::create([
                            'stock_id' => $stock->id,
                            'type' => 'return',
                            'quantity' => $item->quantity,
                            'reference_id' => $orderReturn->id,
                            'reason' => 'Return Received & Inspected (RMA: ' . $orderReturn->rma_number . ')',
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            });

            return back()->with('success', 'Return items inspected and received into inventory.');

        } catch (Exception $e) {
            return back()->with('error', 'Error receiving return: ' . $e->getMessage());
        }
    }
    /**
     * Bulk Print Invoices or COD Receipts
     */
    public function bulkPrint(Request $request)
    {
        // $this->authorize('orders view'); // Ensure this permission exists or use appropriate one

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
            'type' => 'required|in:invoice,cod',
        ]);

        $orders = Order::whereIn('id', $validated['ids'])
            ->with(['items.product', 'customer', 'billingAddress', 'shippingAddress', 'invoices'])
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'No orders selected.');
        }

        if ($validated['type'] === 'invoice') {
            // Filter orders that have invoices
            $ordersWithInvoices = $orders->filter(function ($order) {
                return $order->invoices->isNotEmpty();
            });

            // Flatten to get all invoices
            $invoices = new \Illuminate\Database\Eloquent\Collection($orders->pluck('invoices')->flatten());

            if ($invoices->isEmpty()) {
                return back()->with('error', 'No invoices found for selected orders. Ensure orders are Ready to Ship.');
            }

            // Load relations for invoices
            $invoices->load(['order.customer', 'order.billingAddress', 'order.shippingAddress', 'order.items']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('central.invoices.bulk_invoice', compact('invoices'));
            return $pdf->download('bulk-invoices-' . now()->format('YmdHis') . '.pdf');

        } elseif ($validated['type'] === 'cod') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('central.receipts.bulk_cod', compact('orders'))
                ->setPaper([0, 0, 226, 600]); // Consistent with single COD receipt size
            return $pdf->download('bulk-cod-' . now()->format('YmdHis') . '.pdf');
        }
    }

    /**
     * Bulk Status Update
     */
    public function bulkStatusUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
            'status' => 'required|in:confirmed,processing,ready_to_ship,delivered,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $orders = Order::whereIn('id', $validated['ids'])->get();
            $statusFlow = ['placed', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'];
            $targetStatus = $validated['status'];
            $targetIndex = array_search($targetStatus, $statusFlow);

            $failedOrders = [];

            foreach ($orders as $order) {
                // Skip if status is already same
                if ($order->status === $targetStatus) {
                    continue;
                }

                $currentIndex = array_search($order->status, $statusFlow);

                // Validation Logic
                $isValid = false;

                // Allow cancellation if not delivered
                if ($targetStatus === 'cancelled') {
                    if ($order->status !== 'delivered' && $order->status !== 'cancelled') {
                        $isValid = true;
                    }
                }
                // Forward transition check
                elseif ($currentIndex !== false && $targetIndex !== false) {
                    if ($targetIndex > $currentIndex) {
                        $isValid = true;
                    }
                }

                if (!$isValid) {
                    $failedOrders[] = $order->order_number;
                    continue; // Skip invalid updates
                }

                // If valid, proceed to update
                $order->update([
                    'status' => $validated['status'],
                    'updated_by' => auth()->id(),
                ]);

                // If updated to ready_to_ship, ensure invoice exists
                if ($validated['status'] === 'ready_to_ship' && $order->invoices()->doesntExist()) {
                    Invoice::create([
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                        'issue_date' => now(),
                        'due_date' => now(),
                        'total_amount' => $order->grand_total,
                        'paid_amount' => 0,
                        'status' => 'unpaid',
                    ]);
                }
            }

            DB::commit();

            if (count($failedOrders) > 0) {
                // Determine if it was a reversion attempt for better messaging
                $message = 'Status Update Failed: You cannot revert the status of orders that have already progressed. The following orders were skipped: ' . implode(', ', $failedOrders);
                return back()->with('error', $message);
            }

            return back()->with('success', 'Bulk status update completed successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating orders: ' . $e->getMessage());
        }
    }
}
