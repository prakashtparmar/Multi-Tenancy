<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderReturnController extends Controller
{
    public function index()
    {
        $returns = OrderReturn::with(['order.customer'])->latest()->paginate(10);
        return view('tenant.returns.index', compact('returns'));
    }

    public function create()
    {
        // Ideally we select from Orders that are 'delivered'
        $orders = Order::where('status', '!=', 'cancelled')->latest()->limit(50)->get();
        return view('tenant.returns.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:sellable,damaged',
        ]);

        $order = Order::find($validated['order_id']);

        DB::transaction(function () use ($validated, $order) {
            $rma = OrderReturn::create([
                'rma_number' => 'RMA-' . strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'status' => 'requested',
                'reason' => $validated['reason'],
                'refund_method' => 'credit', // Default
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
    }

    public function show(OrderReturn $orderReturn)
    {
        $orderReturn->load(['items.product', 'order.customer']);
        return view('tenant.returns.show', compact('orderReturn'));
    }

    public function updateStatus(Request $request, OrderReturn $orderReturn)
    {
        $request->validate(['status' => 'required|in:approved,received,refunded,rejected']);

        DB::transaction(function () use ($request, $orderReturn) {
            $orderReturn->update(['status' => $request->status]);

            if ($request->status === 'received') {
                // Restock Logic (Simple)
                foreach ($orderReturn->items as $item) {
                    if ($item->condition === 'sellable') {
                         // Find warehouse from original order
                         $warehouseId = $orderReturn->order->warehouse_id;
                         if ($warehouseId) {
                             $stock = \App\Models\InventoryStock::firstOrCreate(
                                ['warehouse_id' => $warehouseId, 'product_id' => $item->product_id],
                                ['quantity' => 0]
                             );
                             $stock->increment('quantity', $item->quantity);
                             
                             \App\Models\InventoryMovement::create([
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
    }
}
