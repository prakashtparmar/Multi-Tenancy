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
    public function index(): View
    {
        $this->authorize('orders view');
        
        $returns = OrderReturn::with(['order.customer'])->latest()->paginate(10);
        return view('tenant.returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new return.
     */
    public function create(Request $request): View
    {
        $this->authorize('orders manage');

        $preSelectedOrderId = $request->query('order_id');
        $orders = Order::where('status', '!=', 'cancelled')->latest()->limit(50)->get();
        
        $preSelectedOrder = null;
        if ($preSelectedOrderId) {
            $preSelectedOrder = Order::with('items')->find($preSelectedOrderId);
        }

        return view('tenant.returns.create', compact('orders', 'preSelectedOrderId', 'preSelectedOrder'));
    }

    /**
     * Store a newly created return in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:sellable,damaged',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        try {
            DB::transaction(function () use ($validated, $order) {
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
            return back()->withInput()->with('error', 'Failed to request RMA.');
        }
    }

    /**
     * Display the specified return.
     */
    public function show(OrderReturn $orderReturn): View
    {
        $this->authorize('orders view');
        
        $orderReturn->load(['items.product', 'order.customer']);
        return view('tenant.returns.show', compact('orderReturn'));
    }

    /**
     * Update the status of the return and handle restock logic.
     */
    public function updateStatus(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        $this->authorize('orders manage');

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
