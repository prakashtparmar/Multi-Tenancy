<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('po_number', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 10);
        $purchaseOrders = $query->latest()->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('central.purchase_orders.index', compact('purchaseOrders'))->render();
        }

        return view('central.purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        // For simple selection, we might create a specialized API or just load all products
        // In a real app, products would be loaded via AJAX select
        $products = Product::select('id', 'name', 'sku', 'cost_price')->get(); 
        return view('central.purchase_orders.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'po_number' => 'required|unique:purchase_orders,po_number',
            'expected_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $totalCost = 0;
            foreach ($validated['items'] as $item) {
                $totalCost += $item['quantity'] * $item['cost'];
            }

            $po = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'po_number' => $validated['po_number'],
                'status' => 'ordered',
                'expected_date' => $validated['expected_date'],
                'total_cost' => $totalCost,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity'],
                    'unit_cost' => $item['cost'],
                    'total_cost' => $item['quantity'] * $item['cost'],
                ]);
            }
        });

        return redirect()->route('central.purchase-orders.index')->with('success', 'Purchase Order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items.product', 'supplier', 'warehouse']);
        return view('central.purchase_orders.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'Order already received.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->update(['status' => 'received']);

            foreach ($purchaseOrder->items as $item) {
                // Update Stock
                $stock = \App\Models\InventoryStock::firstOrCreate(
                    [
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'product_id' => $item->product_id
                    ],
                    ['quantity' => 0, 'reserve_quantity' => 0]
                );

                $stock->increment('quantity', $item->quantity_ordered);

                // Log Movement
                \App\Models\InventoryMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'purchase',
                    'quantity' => $item->quantity_ordered,
                    'reference_id' => $purchaseOrder->id,
                    'reason' => 'PO Received: ' . $purchaseOrder->po_number,
                    'user_id' => auth()->id(),
                ]);
                
                $item->update(['quantity_received' => $item->quantity_ordered]);
            }
        });

        return back()->with('success', 'Stock received and inventory updated.');
    }
}
