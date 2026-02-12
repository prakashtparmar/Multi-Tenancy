<?php
declare(strict_types=1);
namespace App\Http\Controllers\Central;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
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
     * Display a listing of central orders.
     */
    public function index(Request $request): View
    {
        $this->authorize('orders view');
        $query = Order::with(['customer', 'warehouse', 'creator', 'shipments', 'items']);
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('order_number', 'like', "%{$search}%")->orWhereHas('customer', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', (string) $request->input('payment_status'));
        }

        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', (string) $request->input('shipping_status'));
        }
        $perPage = (int) $request->input('per_page', 10);
        $orders = $query->latest()->paginate($perPage)->withQueryString();
        return view('central.orders.index', compact('orders'));
    }
    /**
     * Show the form for creating a new order.
     */
    public function create(Request $request): View
    {
        $this->authorize('orders create');
        $warehouses = Warehouse::where('is_active', true)->get();
        $customerId = $request->query('customer_id');
        $preSelectedCustomer = $customerId ? Customer::with('addresses')->withCount('orders')->find($customerId) : null;
        $products = Product::where('is_active', true)
            ->with(['stocks', 'images', 'taxClass.rates'])
            ->limit(20)
            ->get()
            ->map(
                fn($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'stock_on_hand' => $product->stock_on_hand,
                    'unit_type' => $product->unit_type,
                    'brand' => $product->brand->name ?? 'N/A',
                    'description' => $product->description,
                    'is_organic' => $product->is_organic,
                    'origin' => $product->origin,
                    'image_url' => $product->image_url,
                    'category' => $product->category->name ?? 'Uncategorized',
                    'default_discount_type' => $product->default_discount_type,
                    'default_discount_value' => $product->default_discount_value,
                    'tax_rate' => $product->tax_rate,
                    'tax_class_id' => $product->tax_class_id,
                    'tax_class' => $product->taxClass ? [
                        'id' => $product->taxClass->id,
                        'name' => $product->taxClass->name,
                        'rates' => $product->taxClass->rates->map(fn($r) => [
                            'rate' => $r->rate,
                            'name' => $r->name
                        ])
                    ] : null,
                ],
            );
        return view('central.orders.create', [
            'customers' => [],
            'warehouses' => $warehouses,
            'products' => $products,
            'preSelectedCustomer' => $preSelectedCustomer,
        ]);
    }
    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('orders create');

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
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {

                $subTotalAmount = 0;
                $itemDiscountsTotal = 0;

                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->with(['taxClass.rates'])->get()->keyBy('id'); // Updated to load Tax Info

                $preparedItems = [];
                $totalTaxAmount = 0; // Track Total Tax

                foreach ($validated['items'] as $item) {

                    $product = $products[$item['product_id']] ?? null; // Get product for Tax Calc

                    $itemBasePrice = $item['quantity'] * $item['price'];

                    $itemDiscountValue = $item['discount_value'] ?? 0;
                    $itemDiscountType = $item['discount_type'] ?? 'fixed';

                    $itemDiscount = $itemDiscountType === 'percent'
                        ? $itemBasePrice * ($itemDiscountValue / 100)
                        : $itemDiscountValue;

                    $subTotalAmount += $itemBasePrice;
                    $itemDiscountsTotal += $itemDiscount;

                    // Tax Calculation
                    $taxDetails = $this->taxService->calculate($product, (float) $item['price'], (float) $item['quantity']);
                    $itemTaxAmount = $taxDetails['amount'];
                    $totalTaxAmount += $itemTaxAmount;

                    $preparedItems[] = array_merge($item, [
                        'discount_amount' => $itemDiscount,
                        'tax_percent' => $taxDetails['rate'], // Store Rate
                        'tax_amount' => $itemTaxAmount,       // Store Amount (Logic Use)
                    ]);
                }

                // Order-level discount
                $orderDiscountType = $validated['discount_type'] ?? 'fixed';
                $orderDiscountValue = $validated['discount_value'] ?? 0;

                $netAfterItems = $subTotalAmount - $itemDiscountsTotal;

                $orderDiscountAmount = $orderDiscountType === 'percent'
                    ? $netAfterItems * ($orderDiscountValue / 100)
                    : $orderDiscountValue;

                // Grand Total = (Subtotal - ItemDisc - OrderDisc) + Tax
                // Note: Typically tax is calculated on the discounted price. 
                // However, simple implementation usually does Tax on Base or Tax on Net.
                // For now, let's assume Tax is Inclusive or Exclusive? 
                // The provided TaxService calculates on Base Price * Quantity.
                // Only if we want Post-Discount Tax we need to adj.
                // Given "Non-disruptive", let's keeps Tax on Line Base for now (Standard GST often on transaction value).

                $grandTotal = ($netAfterItems - $orderDiscountAmount) + $totalTaxAmount;

                // Create Order
                $order = Order::create([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'total_amount' => $subTotalAmount,
                    'discount_amount' => $itemDiscountsTotal + $orderDiscountAmount,
                    'tax_amount' => $totalTaxAmount, // Store Tax Total
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
                ]);

                // Create Order Items (NO INVENTORY TOUCH HERE)
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
                        'cost_price' => $product->cost_price ?? 0, // Snapshot
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'discount_value' => $item['discount_value'] ?? 0,
                        'discount_amount' => $item['discount_amount'],
                        'total_price' => $item['quantity'] * $item['price'],
                        'tax_percent' => $item['tax_percent'] ?? 0,
                    ]);
                }

                return $order;
            });

            if ($request->wantsJson()) {
                // Send Notification
                auth()->user()->notify(new OrderNotification($order, 'created'));

                session()->flash('success', 'Order created successfully.');
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully.',
                    'redirect_url' => route('central.orders.create'),
                ]);
            }

            auth()->user()->notify(new OrderNotification($order, 'created'));
            return redirect()
                ->route('central.orders.create')
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
        return view('central.orders.show', ['order' => $order->load(['items', 'invoices', 'shipments', 'creator', 'updater', 'canceller', 'completer', 'billingAddress', 'shippingAddress', 'warehouse'])]);
    }
    /**
     * Update the specified order's status.
     */
    /**
     * Update the specified order's status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        // $this->authorize('orders manage'); // Legacy broad check

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

                /**
                 * PENDING / SCHEDULED → CONFIRMED (RESERVE STOCK)
                 */
                case 'confirm':
                    $this->orderService->confirmOrder($order);
                    break;

                /**
                 * CONFIRMED → PROCESSING
                 */
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

                /**
                 * PROCESSING → READY TO SHIP
                 * Invoice created here
                 */
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

                /**
                 * READY TO SHIP → SHIPPED (DEDUCT STOCK)
                 */
                case 'ship':
                    if ($order->status !== 'ready_to_ship') {
                        throw new Exception('Order must be Ready to Ship before shipping.');
                    }

                    // ⚠️ Service handles:
                    // - quantity decrement
                    // - reserve decrement
                    // - inventory movement
                    // - shipment creation
                    // - status update
                    $this->orderService->shipOrder(
                        $order,
                        (string) $request->input('tracking_number'),
                        (string) $request->input('carrier')
                    );
                    break;

                /**
                 * SHIPPED → IN TRANSIT
                 */
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

                /**
                 * IN TRANSIT / SHIPPED → COMPLETED
                 */
                case 'deliver':
                    $this->orderService->deliverOrder($order);
                    break;

                /**
                 * CANCEL (RELEASE RESERVE)
                 */
                case 'cancel':
                    $this->orderService->cancelOrder($order);
                    break;

                default:
                    throw new Exception("Invalid action: {$action}");
            }

            auth()->user()->notify(new OrderNotification($order, $action));

            return redirect()
                ->route('central.orders.show', $order)
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

    public function edit(Order $order): View|RedirectResponse
    {
        $this->authorize('orders edit');
        if (in_array($order->status, ['completed', 'delivered', 'cancelled', 'returned'])) {
            return back()->with('error', 'Cannot edit orders that are already delivered, completed, cancelled, or returned.');
        }
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
        $orderData = $order->load(['items', 'customer.addresses', 'customer.interactions']);
        $warehouses = Warehouse::all();
        return view('central.orders.edit', compact('products', 'orderData', 'warehouses'));
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
                /**
                 * If the order is already confirmed/processing, we need to release
                 * the reserves of the OLD items before deleting them.
                 */
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
                    $taxDetails = $this->taxService->calculate($product, (float) $item['price'], (float) $item['quantity']);
                    $itemTaxAmount = $taxDetails['amount'];
                    $totalTaxAmount += $itemTaxAmount;

                    $preparedItems[] = [
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name,
                        'sku' => $product->sku ?? 'N/A',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $lineSubtotal,
                        'discount_type' => $itemDiscountType,
                        'discount_value' => $itemDiscountValue,
                        'discount_amount' => $itemDiscountAmount,
                        'tax_percent' => $taxDetails['rate'],
                    ];
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

                // NO INVENTORY TOUCH HERE

                /**
                 * If the order was in a reserved state, we MUST apply reserves
                 * to the NEW items now.
                 */
                if ($isReservedState) {
                    // We refresh the order to get the new items
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
                    'redirect_url' => route('central.orders.index'),
                ]);
            }
            return redirect()->route('central.orders.index')->with('success', 'Order updated successfully.');
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
    public function downloadInvoice(Order $order)
    {
        try {
            $this->authorize('orders view');
            $order->load(['items.product', 'customer', 'billingAddress', 'shippingAddress']);
            $pdf = Pdf::loadView('central.invoices.print', compact('order'));
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
            $pdf = Pdf::loadView('central.receipts.cod', compact('order'))->setPaper([0, 0, 226, 600]); // 80mm width for thermal
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

    public function bulkPrint(Request $request)
    {
        $this->authorize('orders view');

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
                return back()->with('error', 'No invoices found for selected orders.');
            }

            // Load relations for invoices
            $invoices->load(['order.customer', 'order.billingAddress', 'order.shippingAddress', 'order.items']);

            $pdf = Pdf::loadView('central.invoices.bulk_invoice', compact('invoices'));
            return $pdf->download('bulk-invoices-' . now()->format('YmdHis') . '.pdf');
        } elseif ($validated['type'] === 'cod') {
            $pdf = Pdf::loadView('central.receipts.bulk_cod', compact('orders'))
                ->setPaper([0, 0, 226, 600]); // Consistent with single COD receipt size
            return $pdf->download('bulk-cod-' . now()->format('YmdHis') . '.pdf');
        }

        return back()->with('error', 'Invalid print type.');
    }
}
