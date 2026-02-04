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


class OrderController extends Controller
{
    use AuthorizesRequests;

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of central orders.
     */
    public function index(Request $request): View
    {
        $this->authorize('orders view');

        $query = Order::with(['customer', 'warehouse', 'creator']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
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
        $this->authorize('orders manage');

        $warehouses = Warehouse::where('is_active', true)->get();
        $customerId = $request->query('customer_id');
        $preSelectedCustomer = $customerId ? Customer::with('addresses')->find($customerId) : null;
        
        $products = Product::where('is_active', true)
            ->with(['stocks', 'images'])
            ->limit(20)
            ->get()
            ->map(fn($product) => [
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
                'default_discount_value' => $product->default_discount_value
            ]);

        return view('central.orders.create', [
            'customers' => [], 
            'warehouses' => $warehouses,
            'products' => $products,
            'preSelectedCustomer' => $preSelectedCustomer
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('orders manage');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            // 'order_number' => 'required|unique:orders,order_number',
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
            DB::transaction(function () use ($validated) {
                $subTotalAmount = 0;
                $itemDiscountsTotal = 0;
                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                $preparedItems = [];
                foreach ($validated['items'] as $item) {
                    $itemBasePrice = $item['quantity'] * $item['price'];
                    $itemDiscount = 0;
                    $itemDiscountValue = $item['discount_value'] ?? 0;
                    $itemDiscountType = $item['discount_type'] ?? 'fixed';

                    if ($itemDiscountType === 'percent') {
                        $itemDiscount = $itemBasePrice * ($itemDiscountValue / 100);
                    } else {
                        $itemDiscount = $itemDiscountValue;
                    }

                    $subTotalAmount += $itemBasePrice;
                    $itemDiscountsTotal += $itemDiscount;

                    $preparedItems[] = array_merge($item, [
                        'discount_amount' => $itemDiscount,
                        'total_price_after_discount' => $itemBasePrice - $itemDiscount
                    ]);
                }

                $orderDiscountAmount = 0;
                $orderDiscountType = $validated['discount_type'] ?? 'fixed';
                $orderDiscountValue = $validated['discount_value'] ?? 0;
                $netAfterItems = $subTotalAmount - $itemDiscountsTotal;

                if ($orderDiscountType === 'percent') {
                    $orderDiscountAmount = $netAfterItems * ($orderDiscountValue / 100);
                } else {
                    $orderDiscountAmount = $orderDiscountValue;
                }

                $grandTotal = $netAfterItems - $orderDiscountAmount;

                $order = Order::create([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    // 'order_number' => $validated['order_number'],
                    'total_amount' => $subTotalAmount,
                    'discount_amount' => $itemDiscountsTotal + $orderDiscountAmount,
                    'discount_type' => $orderDiscountType,
                    'discount_value' => $orderDiscountValue,
                    'status' => ($validated['is_future_order'] ?? false) ? 'scheduled' : 'pending',
                    'placed_at' => now(),
                    'scheduled_at' => $validated['scheduled_at'] ?? null,
                    'is_future_order' => $validated['is_future_order'] ?? false,
                    'billing_address_id' => $validated['billing_address_id'] ?? null,
                    'shipping_address_id' => $validated['shipping_address_id'] ?? $validated['billing_address_id'],
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'shipping_method' => $validated['shipping_method'] ?? 'standard',
                    'grand_total' => $grandTotal, 
                    'created_by' => auth()->id(),
                ]);

                foreach ($preparedItems as $item) {
                    $product = $products[$item['product_id']] ?? null;
                    if (!$product) throw new Exception("Product not found.");
                    
                    $order->items()->create([
                        'product_name' => $product->name, 
                        'sku' => $product->sku ?? 'N/A',
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'discount_value' => $item['discount_value'] ?? 0,
                        'discount_amount' => $item['discount_amount'],
                        'total_price' => $item['quantity'] * $item['price'],
                        'tax_percent' => 0,
                    ]);

                    // Update Inventory
                    $stock = InventoryStock::firstOrCreate(
                        ['product_id' => $product->id, 'warehouse_id' => $validated['warehouse_id']],
                        ['quantity' => 0, 'reserve_quantity' => 0]
                    );

                    $stock->decrement('quantity', $item['quantity']);

                    InventoryMovement::create([
                        'stock_id' => $stock->id,
                        'type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'reference_id' => $order->id,
                        'reason' => 'Order Placed: ' . $order->order_number,
                        'user_id' => auth()->id(),
                    ]);

                    $product->refreshStockOnHand();
                }
            });

            if ($request->wantsJson()) {
                session()->flash('success', 'Order created successfully.');
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully.',
                    'redirect_url' => route('central.orders.create') 
                ]);
            }

            return redirect()->route('central.orders.create')->with('success', 'Order created successfully.');

        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('orders view');
        return view('central.orders.show', ['order' => $order->load(['items', 'invoices', 'shipments', 'creator', 'updater', 'canceller', 'completer', 'billingAddress', 'shippingAddress'])]);
    }

    /**
     * Update the specified order's status.
     */
    /**
 * Update the specified order's status.
 */
public function updateStatus(Request $request, Order $order): RedirectResponse
{
    $this->authorize('orders manage');

    // ✅ Always work with latest DB state
    $order->refresh();

    $action = (string) $request->input('action');


    try {
       

        switch ($action) {

        
            /**
             * PENDING / PLACED → CONFIRMED
             */
            case 'confirm':
                $order->update([
                    'status' => 'confirmed',
                    'shipping_status' => 'pending',
                    'updated_by' => auth()->id(),
                ]);
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
             * ✅ Invoice is generated here (once)
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

    // Create invoice only if not exists
    if ($order->invoices()->doesntExist()) {
        Invoice::create([
            'order_id'       => $order->id,
            'customer_id'    => $order->customer_id,
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
            'issue_date'     => now(),
            'due_date'       => now(),
            'total_amount'   => $order->grand_total,
            'paid_amount'    => 0,
            'status'         => 'unpaid',
        ]);
    }
    break;


            /**
             * READY TO SHIP → SHIPPED
             */
            case 'ship':

    if ($order->status !== 'ready_to_ship') {
        throw new Exception('Order must be Ready to Ship before shipping.');
    }

    $this->orderService->shipOrder(
        $order,
        (string) $request->input('tracking_number'),
        (string) $request->input('carrier')
    );

    $order->update([
        'status' => 'shipped',
        'shipping_status' => 'shipped',
        'updated_by' => auth()->id(),
    ]);
    break;


            /**
             * SHIPPED → IN TRANSIT
             */
            case 'in_transit':
                $order->update([
                    'status' => 'in_transit',
                    'shipping_status' => 'in_transit',
                ]);
                break;

            /**
             * IN TRANSIT → DELIVERED / COMPLETED
             */
           case 'deliver':
    if (!in_array($order->status, ['shipped', 'in_transit'])) {
        throw new Exception('Order must be Shipped before delivery.');
    }

    $order->update([
        'status' => 'completed',
        'shipping_status' => 'delivered',
        'completed_by' => auth()->id(),
        'updated_by' => auth()->id(),
    ]);
    break;


            /**
             * CANCEL (ANY STAGE)
             */
            case 'cancel':
                $this->orderService->cancelOrder($order);
                break;

            default:
                throw new Exception("Invalid action: {$action}");
        }

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



    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order): View
    {
        $this->authorize('orders manage');

        if (in_array($order->status, ['completed', 'delivered', 'cancelled', 'returned'])) {
            return back()->with('error', 'Cannot edit orders that are already delivered, completed, cancelled, or returned.');
        }
        
        $products = Product::where('is_active', true)
            ->with(['stocks', 'images'])
            ->limit(20)
            ->get()
            ->map(fn($p) => [
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
                'default_discount_value' => $p->default_discount_value
            ]);

        $orderData = $order->load(['items', 'customer.addresses', 'customer.interactions']);
        $warehouses = Warehouse::all();
        
        return view('central.orders.edit', compact('products', 'orderData', 'warehouses'));
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $this->authorize('orders manage');

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
            'order_status' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($validated, $order) {
                // Restore old inventory
                foreach ($order->items as $oldItem) {
                    $oldStock = InventoryStock::where('product_id', $oldItem->product_id)
                        ->where('warehouse_id', $order->warehouse_id)
                        ->first();
                    
                    if ($oldStock) {
                        $oldStock->increment('quantity', $oldItem->quantity);
                        
                        InventoryMovement::create([
                            'stock_id' => $oldStock->id,
                            'type' => 'adjustment',
                            'quantity' => $oldItem->quantity,
                            'reference_id' => $order->id,
                            'reason' => 'Order Update (Old Items Restored): ' . $order->order_number,
                            'user_id' => auth()->id(),
                        ]);
                        $oldStock->product->refreshStockOnHand();
                    }
                }

                $order->items()->delete();

                $subTotalAmount = 0;
                $itemDiscountsTotal = 0;
                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                $preparedItems = [];
                foreach ($validated['items'] as $item) {
                    $product = $products[$item['product_id']] ?? null;
                    if (!$product) throw new Exception("Product #{$item['product_id']} not found.");

                    $lineSubtotal = $item['quantity'] * $item['price'];
                    $subTotalAmount += $lineSubtotal;

                    $itemDiscountType = $item['discount_type'] ?? 'fixed';
                    $itemDiscountValue = $item['discount_value'] ?? 0;
                    $itemDiscountAmount = 0;

                    if ($itemDiscountType === 'percent') {
                        $itemDiscountAmount = $lineSubtotal * ($itemDiscountValue / 100);
                    } else {
                        $itemDiscountAmount = (float)$itemDiscountValue;
                    }
                    $itemDiscountsTotal += $itemDiscountAmount;

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
                    $orderDiscountAmount = (float)$orderDiscountValue;
                }

                $grandTotal = max(0, $netAfterItemDiscounts - $orderDiscountAmount);

                foreach ($preparedItems as $pItem) {
                    $order->items()->create($pItem);

                    // Deduct new inventory
                    $newStock = InventoryStock::firstOrCreate(
                        ['product_id' => $pItem['product_id'], 'warehouse_id' => $validated['warehouse_id']],
                        ['quantity' => 0, 'reserve_quantity' => 0]
                    );

                    $newStock->decrement('quantity', $pItem['quantity']);

                    InventoryMovement::create([
                        'stock_id' => $newStock->id,
                        'type' => 'sale',
                        'quantity' => -$pItem['quantity'],
                        'reference_id' => $order->id,
                        'reason' => 'Order Update (New Items Deducted): ' . $order->order_number,
                        'user_id' => auth()->id(),
                    ]);

                    if (isset($products[$pItem['product_id']])) {
                        $products[$pItem['product_id']]->refreshStockOnHand();
                    }
                }

                $order->update([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'total_amount' => $subTotalAmount,
                    'discount_amount' => $itemDiscountsTotal + $orderDiscountAmount,
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
                    'updated_by' => auth()->id()
                ]);
            });

            if ($request->wantsJson()) {
                session()->flash('success', 'Order updated successfully.');
                return response()->json([
                    'success' => true,
                    'message' => 'Order updated successfully.',
                    'redirect_url' => route('central.orders.index') 
                ]);
            }

            return redirect()->route('central.orders.index')->with('success', 'Order updated successfully.');

        } catch (\Exception $e) {
            \Log::error("Order Update Error: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'Error updating order: ' . $e->getMessage());
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
            \Log::error("PDF Generation Error (Invoice): " . $e->getMessage());
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function downloadReceipt(Order $order)
    {
        try {
            $this->authorize('orders view');
            $order->load(['items.product', 'customer']);
            
            $pdf = Pdf::loadView('central.receipts.cod', compact('order'))
                      ->setPaper([0, 0, 226, 600]); // 80mm width for thermal
            
            return $pdf->download("receipt-{$order->order_number}.pdf");
        } catch (\Exception $e) {
            \Log::error("PDF Generation Error (Receipt): " . $e->getMessage());
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $this->authorize('orders view');
        $format = $request->input('format', 'csv');
        $filename = "orders-" . date('Y-m-d');

        switch ($format) {
            case 'xlsx':
                return Excel::download(new OrdersExport, "{$filename}.xlsx");
            case 'pdf':
                return Excel::download(new OrdersExport, "{$filename}.pdf", \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return Excel::download(new OrdersExport, "{$filename}.csv");
        }
    }
}
