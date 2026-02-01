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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Exception;

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
                'category' => $product->category->name ?? 'Uncategorized'
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
            'order_number' => 'required|unique:orders,order_number',
            'is_future_order' => 'boolean',
            'scheduled_at' => 'required_if:is_future_order,true|nullable|date|after:now',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'billing_address_id' => 'nullable|integer',
            'shipping_address_id' => 'nullable|integer',
            'payment_method' => 'nullable|string',
            'shipping_method' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $totalAmount = 0;
                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $totalAmount += $item['quantity'] * $item['price'];
                }

                $order = Order::create([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'order_number' => $validated['order_number'],
                    'total_amount' => $totalAmount,
                    'status' => ($validated['is_future_order'] ?? false) ? 'scheduled' : 'pending',
                    'placed_at' => now(),
                    'scheduled_at' => $validated['scheduled_at'] ?? null,
                    'is_future_order' => $validated['is_future_order'] ?? false,
                    'billing_address_id' => $validated['billing_address_id'] ?? null,
                    'shipping_address_id' => $validated['shipping_address_id'] ?? null,
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'shipping_method' => $validated['shipping_method'] ?? 'standard',
                    'grand_total' => $totalAmount, 
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $product = $products[$item['product_id']] ?? null;
                    if (!$product) throw new Exception("Product not found.");
                    
                    $order->items()->create([
                        'product_name' => $product->name, 
                        'sku' => $product->sku ?? 'N/A',
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['quantity'] * $item['price'],
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
        return view('central.orders.show', ['order' => $order->load(['items', 'invoices', 'shipments', 'creator', 'updater', 'canceller', 'completer'])]);
    }

    /**
     * Update the specified order's status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('orders manage');
        $action = (string) $request->input('action');

        try {
            $order->update(['updated_by' => auth()->id()]);
            
            switch ($action) {
                case 'confirm':
                    $this->orderService->confirmOrder($order);
                    break;
                case 'ship':
                    $this->orderService->shipOrder($order, (string) $request->input('tracking_number'));
                    break;
                case 'deliver':
                    $this->orderService->deliverOrder($order);
                    break;
                case 'cancel':
                    $this->orderService->cancelOrder($order);
                    break;
                default:
                    throw new Exception("Invalid action.");
            }
            return back()->with('success', 'Order status updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order): View
    {
        $this->authorize('orders manage');
        
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
                'category' => $p->category->name ?? 'Uncategorized'
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

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'is_future_order' => 'boolean',
            'scheduled_at' => 'required_if:is_future_order,true|nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
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

                $totalAmount = 0;
                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $totalAmount += $item['quantity'] * $item['price'];
                    
                    $product = $products[$item['product_id']] ?? null;
                    if (!$product) throw new Exception("Product not found.");

                    $order->items()->create([
                        'product_name' => $product->name, 
                        'sku' => $product->sku ?? 'N/A',
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['quantity'] * $item['price'],
                    ]);

                    // Deduct new inventory
                    $newStock = InventoryStock::firstOrCreate(
                        ['product_id' => $product->id, 'warehouse_id' => $validated['warehouse_id']],
                        ['quantity' => 0, 'reserve_quantity' => 0]
                    );

                    $newStock->decrement('quantity', $item['quantity']);

                    InventoryMovement::create([
                        'stock_id' => $newStock->id,
                        'type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'reference_id' => $order->id,
                        'reason' => 'Order Update (New Items Deducted): ' . $order->order_number,
                        'user_id' => auth()->id(),
                    ]);

                    $product->refreshStockOnHand();
                }

                $order->update([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'total_amount' => $totalAmount,
                    'grand_total' => $totalAmount,
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

        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }
}
