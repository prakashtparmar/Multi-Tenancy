<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class OrderReturnController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of central order returns.
     */
    public function index(Request $request): View
    {
        $this->authorize('orders view');

        $query = OrderReturn::with(['order.customer', 'items.product']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('rma_number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $returns = $query->latest()->paginate($perPage)->withQueryString();

        return view('central.returns.index', compact('returns'));
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

        return view('central.returns.create', compact('orders', 'preSelectedOrderId', 'preSelectedOrder'));
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

        try {
            DB::transaction(function () use ($validated) {
                $order = Order::with('items')->findOrFail($validated['order_id']);

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
                    $orderItem = $order->items->where('product_id', $requestedItem['product_id'])->first();

                    if (!$orderItem) {
                        throw new Exception("Product ID {$requestedItem['product_id']} does not belong to this order.");
                    }

                    $previouslyReturned = $returnedQuantities[$requestedItem['product_id']] ?? 0;
                    $availableQty = $orderItem->quantity - $previouslyReturned;

                    if ($requestedItem['quantity'] > $availableQty) {
                        throw new Exception("Cannot return {$requestedItem['quantity']} of {$orderItem->product_name}. Only {$availableQty} available to return.");
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

            return redirect()->route('central.returns.index')->with('success', 'RMA Requested.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to request RMA: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified return.
     */
    public function show(OrderReturn $return): View
    {
        $this->authorize('orders view');
        $return->load(['items.product', 'order.customer']);
        return view('central.returns.show', ['orderReturn' => $return]);
    }

    /**
     * Update the status of the return request.
     */
    public function updateStatus(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        $this->authorize('orders manage');
        $validated = $request->validate(['status' => 'required|in:approved,received,refunded,rejected']);

        try {
            DB::transaction(function () use ($validated, $orderReturn) {
                $orderReturn->update(['status' => $validated['status']]);

                if ($validated['status'] === 'received') {
                    // Restock Logic
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
        } catch (Exception $e) {
            return back()->with('error', 'Failed to update RMA status: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for editing the specified return.
     */
    public function edit(OrderReturn $return): View|RedirectResponse
    {
        $this->authorize('orders manage');

        if ($return->status !== 'requested') {
            return redirect()->route('central.returns.show', $return)
                ->with('error', 'Only "Requested" returns can be edited.');
        }

        $return->load(['items.product', 'order.items.product']);

        return view('central.returns.edit', ['orderReturn' => $return]);
    }

    /**
     * Update the specified return in storage.
     */
    public function update(Request $request, OrderReturn $return): RedirectResponse
    {
        $this->authorize('orders manage');

        if ($return->status !== 'requested') {
            return back()->with('error', 'Only "Requested" returns can be edited.');
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:sellable,damaged',
        ]);

        try {
            DB::transaction(function () use ($validated, $return) {
                // Update basic details
                $return->update([
                    'reason' => $validated['reason'],
                ]);

                // Sync items: 
                // 1. Delete items not in the new list
                // 2. Update/Create items from the new list

                // Get current items
                $currentItems = $return->items->keyBy('product_id');
                $newProductIds = collect($validated['items'])->pluck('product_id')->toArray();

                // Delete removed items
                $return->items()->whereNotIn('product_id', $newProductIds)->delete();

                // Update or Create
                foreach ($validated['items'] as $itemData) {
                    $return->items()->updateOrCreate(
                        ['product_id' => $itemData['product_id']],
                        [
                            'quantity' => $itemData['quantity'],
                            'condition' => $itemData['condition']
                        ]
                    );
                }
            });

            return redirect()->route('central.returns.index')->with('success', 'RMA Updated Successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update RMA: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for inspecting the return items.
     */
    public function inspect(OrderReturn $return): View|RedirectResponse
    {
        $this->authorize('orders manage');

        if ($return->status !== 'approved') {
            return redirect()->route('central.returns.show', $return)
                ->with('error', 'Only "Approved" returns can be inspected.');
        }

        $return->load(['items.product', 'order']);

        return view('central.returns.inspect', ['orderReturn' => $return]);
    }

    /**
     * Store the inspection results and update stock.
     */
    public function storeInspection(Request $request, OrderReturn $return): RedirectResponse
    {
        $this->authorize('orders manage');

        if ($return->status !== 'approved') {
            return back()->with('error', 'Only "Approved" returns can be inspected.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:return_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.condition_received' => 'required|in:sellable,damaged',
        ]);

        try {
            DB::transaction(function () use ($validated, $return) {
                foreach ($validated['items'] as $itemData) {
                    $item = $return->items()->findOrFail($itemData['id']);

                    // Update Item Record
                    $item->update([
                        'quantity_received' => $itemData['quantity_received'],
                        'condition_received' => $itemData['condition_received'],
                    ]);

                    // Restock Logic (Only for Sellable Received Items)
                    if ($itemData['condition_received'] === 'sellable' && $itemData['quantity_received'] > 0) {
                        $warehouseId = $return->order->warehouse_id;
                        if ($warehouseId) {
                            $stock = InventoryStock::firstOrCreate(
                                ['warehouse_id' => $warehouseId, 'product_id' => $item->product_id],
                                ['quantity' => 0]
                            );
                            $stock->increment('quantity', $itemData['quantity_received']);

                            InventoryMovement::create([
                                'stock_id' => $stock->id,
                                'type' => 'return',
                                'quantity' => $itemData['quantity_received'],
                                'reference_id' => $return->id,
                                'reason' => 'RMA Received: ' . $return->rma_number,
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }

                $return->update([
                    'status' => 'received',
                    'inspected_by' => auth()->id(),
                    'inspected_at' => now(),
                ]);
            });

            return redirect()->route('central.returns.show', $return)->with('success', 'RMA Processed & Stock Updated.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to process inspection: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for processing the refund.
     */
    public function refund(OrderReturn $return): View|RedirectResponse
    {
        $this->authorize('orders manage');

        if ($return->status !== 'received') {
            return redirect()->route('central.returns.show', $return)
                ->with('error', 'Only "Received" returns can be refunded.');
        }

        $return->load(['items.product', 'order']);

        return view('central.returns.refund', ['orderReturn' => $return]);
    }

    /**
     * Store the refund details.
     */
    public function storeRefund(Request $request, OrderReturn $return): RedirectResponse
    {
        $this->authorize('orders manage');

        if ($return->status !== 'received') {
            return back()->with('error', 'Only "Received" returns can be refunded.');
        }

        $validated = $request->validate([
            'refunded_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $return) {
                // In a real system, you would call your Payment Gateway here (Stripe/PayPal)
                // For now, we record the refund in the database.

                $return->update([
                    'status' => 'refunded',
                    'refunded_amount' => $validated['refunded_amount'],
                ]);
            });

            return redirect()->route('central.returns.show', $return)->with('success', 'Refund Processed Successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }
}
