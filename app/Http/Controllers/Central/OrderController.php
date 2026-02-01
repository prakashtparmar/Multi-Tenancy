<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
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

        $query = Order::with(['customer', 'warehouse']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
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
                'image_url' => $product->images->where('is_primary', true)->first() 
                    ? asset('storage/' . $product->images->where('is_primary', true)->first()->image_path) 
                    : 'https://placehold.co/400x400?text=No+Image',
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
        return view('central.orders.show', ['order' => $order->load(['items', 'invoices', 'shipments'])]);
    }

    /**
     * Update the specified order's status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('orders manage');
        $action = (string) $request->input('action');

        try {
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
}
