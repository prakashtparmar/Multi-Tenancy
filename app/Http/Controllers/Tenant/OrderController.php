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
            'billing_address_id' => 'nullable|integer',
            'shipping_address_id' => 'nullable|integer',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $totalAmount = 0;
                foreach ($validated['items'] as $item) {
                    $totalAmount += $item['quantity'] * $item['price'];
                }

                $order = Order::create([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'order_number' => $validated['order_number'],
                    'total_amount' => $totalAmount,
                    'status' => ($validated['is_future_order'] ?? false) ? 'scheduled' : 'pending',
                    'payment_status' => 'unpaid',
                    'shipping_status' => 'pending',
                    'placed_at' => now(),
                    'scheduled_at' => $validated['scheduled_at'] ?? null,
                    'is_future_order' => $validated['is_future_order'] ?? false,
                    'billing_address_id' => $validated['billing_address_id'] ?? null,
                    'shipping_address_id' => $validated['shipping_address_id'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
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
}
