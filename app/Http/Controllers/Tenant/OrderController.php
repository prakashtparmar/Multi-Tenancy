<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class OrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the orders.
     */
    public function index(): View
    {
        $this->authorize('orders view');
        
        $orders = Order::with(['customer', 'warehouse'])
            ->latest()
            ->paginate(10);
            
        return view('tenant.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(): View
    {
        $this->authorize('orders manage');
        
        $customers = Customer::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        
        return view('tenant.orders.create', compact('customers', 'warehouses'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_number' => 'required|unique:orders,order_number',
            'is_future_order' => 'boolean',
            'scheduled_at' => 'required_if:is_future_order,1|nullable|date|after:now',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|string|in:fixed,percent',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'billing_address_id' => 'nullable|integer',
            'shipping_address_id' => 'nullable|integer',
            'discount_type' => 'nullable|string|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $subTotalAmount = 0;
                $itemDiscountsTotal = 0;

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
                    'order_number' => $validated['order_number'],
                    'total_amount' => $subTotalAmount,
                    'discount_amount' => $itemDiscountsTotal + $orderDiscountAmount,
                    'discount_type' => $orderDiscountType,
                    'discount_value' => $orderDiscountValue,
                    'status' => ($validated['is_future_order'] ?? false) ? 'scheduled' : 'pending',
                    'payment_status' => 'unpaid',
                    'shipping_status' => 'pending',
                    'placed_at' => now(),
                    'scheduled_at' => $validated['scheduled_at'] ?? null,
                    'is_future_order' => $validated['is_future_order'] ?? false,
                    'billing_address_id' => $validated['billing_address_id'] ?? null,
                    'shipping_address_id' => $validated['shipping_address_id'] ?? null,
                    'grand_total' => $grandTotal,
                ]);

                foreach ($preparedItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'discount_value' => $item['discount_value'] ?? 0,
                        'discount_amount' => $item['discount_amount'],
                        'total_price' => $item['quantity'] * $item['price'],
                    ]);

                    // Update Inventory
                    $stock = InventoryStock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $validated['warehouse_id']],
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

                    // Sync denormalized stock
                    Product::find($item['product_id'])->refreshStockOnHand();
                }

                return redirect()->route('tenant.orders.show', $order)->with('success', 'Order created successfully.');
            });
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('orders view');
        
        $order->load(['customer', 'warehouse', 'items.product', 'invoices', 'shipments']);
        return view('tenant.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order): View
    {
        $this->authorize('orders manage');
        
        if ($order->status === 'completed' || $order->status === 'cancelled') {
             return back()->with('error', 'Cannot edit completed or cancelled orders.');
        }

        $order->load(['items.product']);
        $customers = Customer::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        
        return view('tenant.orders.edit', compact('order', 'customers', 'warehouses'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('orders manage');

        if ($order->status === 'completed' || $order->status === 'cancelled') {
             return back()->with('error', 'Cannot edit completed or cancelled orders.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'is_future_order' => 'boolean',
            'scheduled_at' => 'required_if:is_future_order,1|nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|string|in:fixed,percent',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($validated, $order) {
                // 1. Restore Stock for Old Items
                foreach ($order->items as $item) {
                     $stock = InventoryStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $order->warehouse_id)
                        ->first();
                    
                    if ($stock) {
                        $stock->increment('quantity', $item->quantity);
                        // Log movement (optional, acts as reversal)
                    }
                }
                
                // 2. Clear Old Items
                $order->items()->delete();

                // 3. Process New Items Calculation
                $subTotalAmount = 0;
                $itemDiscountsTotal = 0;
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

                // 4. Update Order
                $order->update([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'total_amount' => $subTotalAmount,
                    'discount_amount' => $itemDiscountsTotal + $orderDiscountAmount,
                    'discount_type' => $orderDiscountType,
                    'discount_value' => $orderDiscountValue,
                    'status' => ($validated['is_future_order'] ?? false) ? 'scheduled' : 'pending',
                    'scheduled_at' => $validated['scheduled_at'] ?? null,
                    'is_future_order' => $validated['is_future_order'] ?? false,
                    'grand_total' => $grandTotal,
                ]);

                // 5. Create New Items and Deduct Stock
                foreach ($preparedItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'discount_value' => $item['discount_value'] ?? 0,
                        'discount_amount' => $item['discount_amount'],
                        'total_price' => $item['quantity'] * $item['price'],
                    ]);

                    // Update Inventory
                    $stock = InventoryStock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $validated['warehouse_id']],
                        ['quantity' => 0, 'reserve_quantity' => 0]
                    );

                    $stock->decrement('quantity', $item['quantity']);

                    // Denormalize
                    Product::find($item['product_id'])->refreshStockOnHand();
                }

                return redirect()->route('tenant.orders.show', $order)->with('success', 'Order updated successfully.');
            });
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }
}
