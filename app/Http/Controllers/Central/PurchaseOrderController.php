<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class PurchaseOrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of central purchase orders.
     */
    public function index(Request $request): View
    {
        $this->authorize('inventory manage');

        $query = PurchaseOrder::with(['supplier', 'warehouse']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('po_number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $purchaseOrders = $query->latest()->paginate($perPage)->withQueryString();

        return view('central.purchase_orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create(): View
    {
        $this->authorize('inventory manage');

        $suppliers = Supplier::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::select('id', 'name', 'sku', 'price')->get(); 
        
        return view('central.purchase_orders.create', compact('suppliers', 'warehouses', 'products'));
    }

    /**
     * Store a newly created purchase order in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('inventory manage');

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

        try {
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

            return redirect()->route('central.purchase-orders.index')->with('success', 'Purchase Order created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('inventory manage');
        $purchaseOrder->load(['items.product', 'supplier', 'warehouse']);
        return view('central.purchase_orders.show', compact('purchaseOrder'));
    }

    /**
     * Mark the purchase order as received and update stock.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('inventory manage');

        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'Order already received.');
        }

        try {
            DB::transaction(function () use ($purchaseOrder) {
                $purchaseOrder->update(['status' => 'received']);

                foreach ($purchaseOrder->items as $item) {
                    $stock = InventoryStock::firstOrCreate(
                        [
                            'warehouse_id' => $purchaseOrder->warehouse_id,
                            'product_id' => $item->product_id
                        ],
                        ['quantity' => 0, 'reserve_quantity' => 0]
                    );

                    $stock->increment('quantity', $item->quantity_ordered);

                    InventoryMovement::create([
                        'stock_id' => $stock->id,
                        'type' => 'purchase',
                        'quantity' => $item->quantity_ordered,
                        'reference_id' => $purchaseOrder->id,
                        'reason' => 'PO Received: ' . $purchaseOrder->po_number,
                        'user_id' => auth()->id(),
                    ]);
                    
                    $item->update(['quantity_received' => $item->quantity_ordered]);
                    
                    // Sync denormalized stock
                    $item->product->refreshStockOnHand();
                }
            });

            return back()->with('success', 'Stock received and inventory updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to receive stock: ' . $e->getMessage());
        }
    }
}
