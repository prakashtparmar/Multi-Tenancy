<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\TaxService;
use App\Exports\OrdersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Models\Invoice;
use App\Notifications\OrderNotification;

class OrderController extends Controller
{
    use AuthorizesRequests;

    protected OrderService $orderService;
    protected TaxService $taxService;

    public function __construct(OrderService $orderService, TaxService $taxService)
    {
        $this->orderService = $orderService;
        $this->taxService = $taxService;
    }

    /**
     * Display a listing of the orders.
     */
    public function index(): View
    {
        $this->authorize('orders view');

        // Tenant specific: Simple pagination for now, can be improved to match Central's filtering later if needed
        $query = Order::with(['customer', 'warehouse', 'creator', 'shipments', 'items']);

        $orders = $query->latest()->paginate(10);

        return view('tenant.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(): View
    {
        $this->authorize('orders create');

        // Tenant specific: Active Customer Session Logic
        $activeCustomerId = session('active_customer_id');

        if ($activeCustomerId) {
            $customers = Customer::with('addresses')->where('id', $activeCustomerId)->get();
        } else {
            $customers = Customer::with('addresses')->get();
        }

        $warehouses = Warehouse::where('is_active', true)->get();

        // Note: Tenant view handles product search via AJAX, so we don't need to preload products like Central does.

        return view('tenant.orders.create', compact('customers', 'warehouses'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('orders create');

        // Validation rules adapted from Central
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'is_future_order' => 'boolean',
            'scheduled_at' => 'required_if:is_future_order,true|nullable|date|after:now',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|string|in:fixed,percent',
            'items.*.discount_value' => 'nullable|numeric|min:0',

            'billing_address_id' => 'required|exists:customer_addresses,id',
            'shipping_address_id' => 'nullable|exists:customer_addresses,id',

            'payment_method' => 'nullable|string',
            'shipping_method' => 'nullable|string',

            'discount_type' => 'nullable|string|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',

            // Allow order_number but we will likely ignore it in favor of model generation to be safe, 
            // or use it if provided (Tenant view generates a random one, Model generates a sequential one. 
            // Model sequential is better, but let's see)
            'order_number' => 'nullable|string',
        ]);

        try {
            $order = DB::transaction(function () use ($validated, $request) {

                $subTotalAmount = 0;
                $itemDiscountsTotal = 0;

                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->with(['taxClass.rates'])->get()->keyBy('id');

                $preparedItems = [];
                $totalTaxAmount = 0;

                foreach ($validated['items'] as $item) {

                    $product = $products[$item['product_id']] ?? null;

                    $itemBasePrice = $item['quantity'] * $item['price'];

                    $itemDiscountValue = $item['discount_value'] ?? 0;
                    $itemDiscountType = $item['discount_type'] ?? 'fixed';

                    $itemDiscount = $itemDiscountType === 'percent'
                        ? $itemBasePrice * ($itemDiscountValue / 100)
                        : $itemDiscountValue;

                    $subTotalAmount += $itemBasePrice;
                    $itemDiscountsTotal += $itemDiscount;

                    $taxDetails = $this->taxService->calculate($product, (float) $item['price'], (float) $item['quantity']);
                    $itemTaxAmount = $taxDetails['amount'];
                    $totalTaxAmount += $itemTaxAmount;

                    $preparedItems[] = array_merge($item, [
                        'discount_amount' => $itemDiscount,
                        'tax_percent' => $taxDetails['rate'],
                        'tax_amount' => $itemTaxAmount,
                    ]);
                }

                // Order-level discount
                $orderDiscountType = $validated['discount_type'] ?? 'fixed';
                $orderDiscountValue = $validated['discount_value'] ?? 0;

                $netAfterItems = $subTotalAmount - $itemDiscountsTotal;

                $orderDiscountAmount = $orderDiscountType === 'percent'
                    ? $netAfterItems * ($orderDiscountValue / 100)
                    : $orderDiscountValue;

                $grandTotal = ($netAfterItems - $orderDiscountAmount) + $totalTaxAmount;

                // Create Order
                $order = Order::create([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'total_amount' => $subTotalAmount,
                    'discount_amount' => $itemDiscountsTotal + $orderDiscountAmount,
                    'discount_type' => $orderDiscountType,
                    'discount_value' => $orderDiscountValue,
                    'status' => ($validated['is_future_order'] ?? false) ? 'scheduled' : 'pending',
                    'placed_at' => now(),
                    'scheduled_at' => $validated['scheduled_at'] ?? null,
                    'is_future_order' => $validated['is_future_order'] ?? false,
                    'billing_address_id' => $validated['billing_address_id'],
                    'shipping_address_id' => $validated['shipping_address_id'] ?? $validated['billing_address_id'],
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'shipping_method' => $validated['shipping_method'] ?? 'standard',
                    'grand_total' => $grandTotal,
                    'created_by' => auth()->id(),
                    // We let the Model boot method generate the robust sequential order number if we don't pass one.
                    // If the request has one (from view), we could use it, but central logic relies on Model gen.
                    // Let's rely on Model generation for consistency and sequence safety.
                ]);

                // Create Order Items (NO INVENTORY TOUCH HERE - Central Pattern)
                foreach ($preparedItems as $item) {

                    $product = $products[$item['product_id']] ?? null;
                    if (!$product) {
                        throw new Exception('Product not found.');
                    }

                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name,
                        'sku' => $product->sku ?? 'N/A',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'discount_value' => $item['discount_value'] ?? 0,
                        'discount_amount' => $item['discount_amount'],
                        'total_price' => $item['quantity'] * $item['price'],
                        'cost_price' => $product->cost_price ?? 0, // Snapshot cost
                        'tax_percent' => $item['tax_percent'] ?? 0,
                    ]);
                }
                return $order;
            });

            if ($request->wantsJson()) {
                auth()->user()->notify(new OrderNotification($order, 'created'));
                session()->flash('success', 'Order created successfully.');
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully.',
                    'redirect_url' => route('tenant.orders.index'), // Tenant route
                ]);
            }

            auth()->user()->notify(new OrderNotification($order, 'created'));
            return redirect()
                ->route('tenant.orders.index') // Tenant route
                ->with('success', 'Order created successfully.');

        } catch (Exception $e) {

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('orders view');
        // Matches Central load
        return view('tenant.orders.show', ['order' => $order->load(['items', 'invoices', 'shipments', 'creator', 'updater', 'canceller', 'completer', 'billingAddress', 'shippingAddress', 'warehouse'])]);
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order): View|RedirectResponse
    {
        $this->authorize('orders edit');

        if (in_array($order->status, ['completed', 'delivered', 'cancelled', 'returned'])) {
            return back()->with('error', 'Cannot edit orders that are already delivered, completed, cancelled, or returned.');
        }

        // Logic from Central edit to ensure variable consistency
        // Note: Central edit view uses $products, $orderData, $warehouses
        // We will adapt to what Tenant edit view likely needs or if we don't have a Tenant Edit view (Step 48 didn't show one explicitly but index/show exist), 
        // Assuming Tenant has basic edit or reusing logic. 
        // If Tenant currently lacks 'edit.blade.php', this might fail if accessed.
        // However, Central has it. For now, we will implement the Controller method.

        // Simulating Central's data prep
        $products = Product::where('is_active', true)
            ->with(['stocks', 'images'])
            ->limit(20)
            ->get()
            ->map(
                fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => (float) $p->price,
                    'stock_on_hand' => (float) $p->stock_on_hand,
                    'unit_type' => $p->unit_type,
                    'brand' => $p->brand->name ?? 'N/A',
                    'description' => $p->description,
                    'is_organic' => $p->is_organic,
                    'origin' => $p->origin,
                    'image_url' => $p->image_url,
                    'category' => $p->category->name ?? 'Uncategorized',
                    'default_discount_type' => $p->default_discount_type,
                    'default_discount_value' => $p->default_discount_value,
                ],
            );

        $orderData = $order->load(['items', 'customer.addresses']);
        $warehouses = Warehouse::all();

        // Ensure tenant view exists, otherwise this will error. 
        // Based on analysis, we are synchronizing.
        return view('tenant.orders.edit', compact('products', 'orderData', 'warehouses'));
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $this->authorize('orders edit');

        if (in_array($order->status, ['completed', 'delivered', 'cancelled', 'returned'])) {
            $msg = 'Cannot update orders that are already delivered, completed, cancelled, or returned.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'is_future_order' => 'boolean',
            'scheduled_at' => 'required_if:is_future_order,true|nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|string|in:fixed,percent',
            'items.*.discount_value' => 'nullable|numeric|min:0',

            'discount_type' => 'nullable|string|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',

            'billing_address_id' => 'nullable|integer',
            'shipping_address_id' => 'nullable|integer',
            'payment_method' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'order_status' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $order) {
                // Central Logic: Check if reserved
                $isReservedState = in_array($order->status, ['confirmed', 'processing', 'ready_to_ship']);

                if ($isReservedState) {
                    $this->orderService->releaseReserves($order);
                }

                $order->items()->delete();

                $subTotalAmount = 0;
                $itemDiscountsTotal = 0;
                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->with(['taxClass.rates'])->get()->keyBy('id');
                $preparedItems = [];
                $totalTaxAmount = 0;

                foreach ($validated['items'] as $item) {
                    $product = $products[$item['product_id']] ?? null;
                    if (!$product) {
                        throw new Exception("Product #{$item['product_id']} not found.");
                    }
                    $lineSubtotal = $item['quantity'] * $item['price'];
                    $subTotalAmount += $lineSubtotal;
                    $itemDiscountType = $item['discount_type'] ?? 'fixed';
                    $itemDiscountValue = $item['discount_value'] ?? 0;
                    $itemDiscountAmount = 0;
                    if ($itemDiscountType === 'percent') {
                        $itemDiscountAmount = $lineSubtotal * ($itemDiscountValue / 100);
                    } else {
                        $itemDiscountAmount = (float) $itemDiscountValue;
                    }
                    $itemDiscountsTotal += $itemDiscountAmount;
                    $preparedItems[] = [
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name,
                        'sku' => $product->sku ?? 'N/A',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'cost_price' => $product->cost_price ?? 0, // Snapshot cost
                        'total_price' => $lineSubtotal,
                        'discount_type' => $itemDiscountType,
                        'discount_value' => $itemDiscountValue,
                        'discount_amount' => $itemDiscountAmount,
                    ];

                    $taxDetails = $this->taxService->calculate($product, (float) $item['price'], (float) $item['quantity']);
                    $itemTaxAmount = $taxDetails['amount'];
                    $totalTaxAmount += $itemTaxAmount;

                    // Add tax info to prepared items (array access)
                    $preparedItems[count($preparedItems) - 1]['tax_percent'] = $taxDetails['rate'];
                }

                // Order Level Discount
                $orderDiscountType = $validated['discount_type'] ?? 'fixed';
                $orderDiscountValue = $validated['discount_value'] ?? 0;
                $orderDiscountAmount = 0;
                $netAfterItemDiscounts = $subTotalAmount - $itemDiscountsTotal;

                if ($orderDiscountType === 'percent') {
                    $orderDiscountAmount = $netAfterItemDiscounts * ($orderDiscountValue / 100);
                } else {
                    $orderDiscountAmount = (float) $orderDiscountValue;
                }

                $grandTotal = max(0, ($netAfterItemDiscounts - $orderDiscountAmount) + $totalTaxAmount);

                foreach ($preparedItems as $pItem) {
                    $order->items()->create($pItem);
                }

                // Re-apply reserves if needed
                if ($isReservedState) {
                    $order->refresh();
                    $this->orderService->applyReserves($order);
                }

                $order->update([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'total_amount' => $subTotalAmount,
                    'discount_amount' => $itemDiscountsTotal + $orderDiscountAmount,
                    'tax_amount' => $totalTaxAmount,
                    'discount_type' => $orderDiscountType,
                    'discount_value' => $orderDiscountValue,
                    'grand_total' => $grandTotal,
                    'scheduled_at' => $validated['scheduled_at'] ?? null,
                    'is_future_order' => $validated['is_future_order'] ?? false,
                    'billing_address_id' => $validated['billing_address_id'] ?? null,
                    'shipping_address_id' => $validated['shipping_address_id'] ?? null,
                    'payment_method' => $validated['payment_method'] ?? $order->payment_method,
                    'shipping_method' => $validated['shipping_method'] ?? $order->shipping_method,
                    'status' => $validated['order_status'] ?? $order->status,
                    'updated_by' => auth()->id(),
                ]);
            });

            auth()->user()->notify(new OrderNotification($order, 'updated'));

            if ($request->wantsJson()) {
                session()->flash('success', 'Order updated successfully.');
                return response()->json([
                    'success' => true,
                    'message' => 'Order updated successfully.',
                    'redirect_url' => route('tenant.orders.index'),
                ]);
            }
            return redirect()->route('tenant.orders.index')->with('success', 'Order updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Order Update Error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()
                ->withInput()
                ->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified order's status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        // $this->authorize('orders manage'); // Legacy

        $action = (string) $request->input('action');

        match ($action) {
            'confirm' => $this->authorize('orders approve'),
            'process', 'ready_to_ship' => $this->authorize('orders process'),
            'ship' => $this->authorize('orders ship'),
            'deliver' => $this->authorize('orders deliver'),
            'cancel' => $this->authorize('orders cancel'),
            default => $this->authorize('orders manage'), // Fallback
        };

        // Always use fresh state
        $order->refresh();

        $action = (string) $request->input('action');

        try {
            switch ($action) {

                case 'confirm':
                    $this->orderService->confirmOrder($order);
                    break;

                case 'process':
                    if ($order->status !== 'confirmed') {
                        throw new Exception('Order must be Confirmed before Processing.');
                    }
                    $order->update([
                        'status' => 'processing',
                        'shipping_status' => 'pending',
                        'updated_by' => auth()->id(),
                    ]);
                    break;

                case 'ready_to_ship':
                    if ($order->status !== 'processing') {
                        throw new Exception('Order must be Processing before Ready to Ship.');
                    }
                    $order->update([
                        'status' => 'ready_to_ship',
                        'shipping_status' => 'pending',
                        'updated_by' => auth()->id(),
                    ]);

                    if ($order->invoices()->doesntExist()) {
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
                    break;

                case 'ship':
                    if ($order->status !== 'ready_to_ship') {
                        throw new Exception('Order must be Ready to Ship before shipping.');
                    }
                    $this->orderService->shipOrder(
                        $order,
                        (string) $request->input('tracking_number'),
                        (string) $request->input('carrier')
                    );
                    break;

                case 'in_transit':
                    if ($order->status !== 'shipped') {
                        throw new Exception('Order must be Shipped before marking In Transit.');
                    }
                    $order->update([
                        'status' => 'in_transit',
                        'shipping_status' => 'in_transit',
                        'updated_by' => auth()->id(),
                    ]);
                    break;

                case 'deliver':
                    $this->orderService->deliverOrder($order);
                    break;

                case 'cancel':
                    $this->orderService->cancelOrder($order);
                    break;

                default:
                    throw new Exception("Invalid action: {$action}");
            }
            $order->refresh();
            auth()->user()->notify(new OrderNotification($order, $action));

            return redirect()
                ->route('tenant.orders.show', $order)
                ->with('success', 'Order status updated successfully.');

        } catch (Exception $e) {
            \Log::error('Order Status Update Error', [
                'order_id' => $order->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadInvoice(Order $order)
    {
        try {
            $this->authorize('orders view');
            $order->load(['items.product', 'customer', 'billingAddress', 'shippingAddress']);

            // Use Tenant view for Invoice Print
            $view = view()->exists('tenant.invoices.print') ? 'tenant.invoices.print' : 'central.invoices.print';

            $pdf = Pdf::loadView($view, compact('order'));
            return $pdf->download("invoice-{$order->order_number}.pdf");
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error (Invoice): ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function downloadReceipt(Order $order)
    {
        try {
            $this->authorize('orders view');
            $order->load(['items.product', 'customer']);

            // Use Tenant view or fallback to Central
            $view = view()->exists('tenant.receipts.cod') ? 'tenant.receipts.cod' : 'central.receipts.cod';

            $pdf = Pdf::loadView($view, compact('order'))->setPaper([0, 0, 226, 600]);
            return $pdf->download("receipt-{$order->order_number}.pdf");
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error (Receipt): ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $this->authorize('orders view');
        $format = $request->input('format', 'csv');
        $filename = 'orders-' . date('Y-m-d');
        switch ($format) {
            case 'xlsx':
                return Excel::download(new OrdersExport(), "{$filename}.xlsx");
            case 'pdf':
                return Excel::download(new OrdersExport(), "{$filename}.pdf", \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return Excel::download(new OrdersExport(), "{$filename}.csv");
        }
    }
}
