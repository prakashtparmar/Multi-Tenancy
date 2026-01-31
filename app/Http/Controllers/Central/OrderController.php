<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Services\OrderService; // Added
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService; // Added

    public function __construct(OrderService $orderService) // Modified to use type hint for OrderService
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'warehouse']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->latest()->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('central.orders.index', compact('orders'))->render();
        }

        return view('central.orders.index', compact('orders'));
    }

    public function create()
    {
        // Performance: Don't load all customers. Use AJAX.
        $customers = []; 
        $warehouses = Warehouse::where('is_active', true)->get();
        
        // Pass top 20 products with price/image for immediate display
        $products = \App\Models\Product::where('is_active', true)
            ->with(['stocks', 'images'])
            ->limit(20)
            ->get()
            ->map(function($product) {
                $primaryImage = $product->images->where('is_primary', true)->first();
                $imageUrl = $primaryImage ? asset('storage/' . $primaryImage->image_path) : 'https://placehold.co/400x400?text=No+Image';

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'stock_on_hand' => $product->stock_on_hand,
                    'unit_type' => $product->unit_type,
                    'image_url' => $imageUrl,
                    'category' => $product->category->name ?? 'Uncategorized'
                ];
            });

        return view('central.orders.create', compact('customers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'billing_address_id' => 'required|exists:customer_addresses,id',
            'shipping_address_id' => 'required|exists:customer_addresses,id',
            'order_number' => 'required|unique:orders,order_number',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Fix: Retrieve the order created in the transaction
        // Since we didn't return it, we need to refactor. 
        // Best practice: Return the object from the transaction.
        $order = \DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            
            // Eager load products to prevent N+1 and check stock
            $productIds = collect($validated['items'])->pluck('product_id');
            $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($validated['items'] as $item) {
                // Stock Check
                $product = $products[$item['product_id']] ?? null;
                if (!$product) throw new \Exception("Product ID {$item['product_id']} not found.");
                
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $order = Order::create([
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_number' => $validated['order_number'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'placed_at' => now(),
                'grand_total' => $totalAmount, 
            ]);

            foreach ($validated['items'] as $item) {
                $product = $products[$item['product_id']];
                
                $order->items()->create([
                    'product_name' => $product->name, 
                    'sku' => $product->sku ?? 'N/A',
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);
            }
            
            return $order;
        });

        if ($request->wantsJson()) {
            session()->flash('success', 'Order created successfully.');
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                // Redirect to create page to start new order (Search Customer step)
                'redirect_url' => route('central.orders.create') 
            ]);
        }

        return redirect()->route('central.orders.create')->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $order->load(['items', 'invoices', 'shipments']);
        return view('central.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load('items');
        $customers = Customer::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        // Pass products with price and stock for frontend logic
        $products = \App\Models\Product::with('stocks')->get()->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'stock_on_hand' => $product->stock_on_hand,
                'unit_type' => $product->unit_type,
            ];
        });

        return view('central.orders.edit', compact('order', 'customers', 'warehouses', 'products'));
    }
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'required',
            'warehouse_id' => 'required',
            'order_number' => 'required|unique:orders,order_number,' . $order->id,
            'items' => 'required|array',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        \DB::transaction(function () use ($order, $validated) {
            $totalAmount = 0;
            
            // Calculate Total
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            // Update Order Header
            $order->update([
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_number' => $validated['order_number'],
                'total_amount' => $totalAmount,
                'grand_total' => $totalAmount,
            ]);

            // Sync Items: Simplest is Delete All & Re-create
            // (Note: In a high-traffic production system, we might differ logic for 'processed' orders)
            $order->items()->delete();

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_name' => \App\Models\Product::find($item['product_id'])->name,
                    'sku' => \App\Models\Product::find($item['product_id'])->sku ?? 'N/A',
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);
            }
        });

        return redirect()->route('central.orders.index')->with('success', 'Order updated successfully.');
    }
    public function updateStatus(Request $request, Order $order)
    {
        $action = $request->input('action'); // confirm, ship, cancel, deliver

        try {
            switch ($action) {
                case 'confirm':
                    $this->orderService->confirmOrder($order);
                    break;
                case 'ship':
                    $this->orderService->shipOrder($order, $request->input('tracking_number'));
                    break;
                case 'deliver':
                    $this->orderService->deliverOrder($order);
                    break;
                case 'cancel':
                    $this->orderService->cancelOrder($order);
                    break;
            }
            return back()->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
