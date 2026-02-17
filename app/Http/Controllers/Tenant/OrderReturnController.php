<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderReturnController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the order returns.
     */
    public function index(Request $request): View
    {
        $this->authorize('returns view');

        // Stats for the tabs
        $stats = [
            'all' => OrderReturn::count(),
            'requested' => OrderReturn::where('status', 'requested')->count(),
            'approved' => OrderReturn::where('status', 'approved')->count(),
            'received' => OrderReturn::where('status', 'received')->count(),
            'refunded' => OrderReturn::where('status', 'refunded')->count(),
            'rejected' => OrderReturn::where('status', 'rejected')->count(),
        ];

        $query = OrderReturn::with(['order.customer', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rma_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->latest()->paginate(10);

        return view('tenant.returns.index', compact('returns', 'stats'));
    }

    /**
     * Show the form for creating a new return.
     */
    public function create(Request $request): View
    {
        $this->authorize('returns create');

        $preSelectedOrderId = $request->query('order_id');
        $orders = Order::with('items.product')->whereIn('status', ['shipped', 'delivered'])->latest()->limit(50)->get();

        $preSelectedOrder = null;
        if ($preSelectedOrderId) {
            $preSelectedOrder = Order::with('items.product')->find($preSelectedOrderId);
            if ($preSelectedOrder && !in_array($preSelectedOrder->status, ['shipped', 'delivered'])) {
                $preSelectedOrder = null;
                session()->flash('error', 'Selected order is not eligible for return (Status: ' . ucfirst($preSelectedOrder->status ?? 'Unknown') . ')');
            }
        }

        return view('tenant.returns.create', compact('orders', 'preSelectedOrderId', 'preSelectedOrder'));
    }

    /**
     * Store a newly created return in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('returns create');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:sellable,damaged',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $order = Order::with('items.product')->findOrFail($validated['order_id']);

                if (!in_array($order->status, ['shipped', 'delivered'])) {
                    throw new \Exception("Returns are only allowed for Shipped or Delivered orders. Current status: " . ucfirst($order->status));
                }

                // Calculate already returned quantities for this order (excluding rejected returns)
                $existingReturns = OrderReturn::with('items')
                    ->where('order_id', $order->id)
                    ->where('status', '!=', 'rejected')
                    ->get();

                $returnedQuantities = [];
                foreach ($existingReturns as $existingReturn) {
                    foreach ($existingReturn->items as $item) {
                        if (!isset($returnedQuantities[$item->product_id])) {
                            $returnedQuantities[$item->product_id] = 0;
                        }
                        $returnedQuantities[$item->product_id] += $item->quantity;
                    }
                }

                // Validate requested quantities against available quantities
                foreach ($validated['items'] as $requestedItem) {
                    // Get ALL line items for this product to calculate total purchaesd qty
                    $orderItems = $order->items->where('product_id', $requestedItem['product_id']);

                    if ($orderItems->isEmpty()) {
                        throw new \Exception("Product ID {$requestedItem['product_id']} does not belong to this order.");
                    }

                    $totalPurchasedQty = (float) $orderItems->sum('quantity');
                    $productName = $orderItems->first()->product->name ?? 'Unknown Product'; // Safe because we checked isEmpty

                    $previouslyReturned = (float) ($returnedQuantities[$requestedItem['product_id']] ?? 0);

                    // Collect RMA numbers for this product
                    $blockingRmas = $existingReturns->filter(function ($rma) use ($requestedItem) {
                        // Check if this RMA contains the current product
                        $hasProduct = false;
                        foreach ($rma->items as $item) {
                            if ($item->product_id == $requestedItem['product_id']) {
                                $hasProduct = true;
                                break;
                            }
                        }
                        return $hasProduct;
                    })->pluck('rma_number')->implode(', ');

                    // Rounding to avoid floating point issues
                    $availableQty = round($totalPurchasedQty - $previouslyReturned, 3);
                    $requestedQty = (float) $requestedItem['quantity'];

                    if ($requestedQty > $availableQty) {
                        $errorMsg = "Cannot return " . (int) $requestedQty . " of {$productName}. Only " . (int) $availableQty . " available. (Purchased: " . (int) $totalPurchasedQty . ", Previously Returned/Requested: " . (int) $previouslyReturned . ")";
                        if (!empty($blockingRmas)) {
                            $errorMsg .= " [Blocking RMAs: {$blockingRmas}]";
                        }
                        throw new \Exception($errorMsg);
                    }
                }

                $rma = OrderReturn::create([
                    'rma_number' => 'RMA-' . strtoupper(Str::random(8)),
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'status' => 'requested',
                    'reason' => $validated['reason'],
                    'refund_method' => 'credit',
                ]);

                foreach ($validated['items'] as $item) {
                    $rma->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'condition' => $item['condition'],
                    ]);
                }
            });

            return redirect()->route('tenant.returns.index')->with('success', 'RMA Requested.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to request RMA: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified return.
     */
    public function show(OrderReturn $orderReturn): View
    {
        $this->authorize('returns view');

        $orderReturn->load(['items.product', 'order.customer']);
        return view('tenant.returns.show', compact('orderReturn'));
    }

    /**
     * Update the status of the return and handle restock logic.
     */
    public function updateStatus(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        $this->authorize('returns edit');

        $request->validate(['status' => 'required|in:approved,received,refunded,rejected']);

        try {
            DB::transaction(function () use ($request, $orderReturn) {
                $orderReturn->update(['status' => $request->status]);

                if ($request->status === 'received') {
                    foreach ($orderReturn->items as $item) {
                        if ($item->condition === 'sellable') {
                            $warehouseId = $orderReturn->order->warehouse_id;
                            if ($warehouseId) {
                                $stock = InventoryStock::firstOrCreate(
                                    ['warehouse_id' => $warehouseId, 'product_id' => $item->product_id],
                                    ['quantity' => 0]
                                );
                                $stock->increment('quantity', $item->quantity);

                                InventoryMovement::create([
                                    'stock_id' => $stock->id,
                                    'type' => 'return',
                                    'quantity' => $item->quantity,
                                    'reference_id' => $orderReturn->id,
                                    'reason' => 'RMA Received: ' . $orderReturn->rma_number,
                                    'user_id' => auth()->id(),
                                ]);
                            }
                        }
                    }
                }
            });

            return back()->with('success', 'RMA Status Updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status.');
        }
    }
}
