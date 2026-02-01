<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('inventory manage');

        $query = Product::with(['stocks.warehouse', 'category']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(15)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('tenant.inventory.index', compact('products', 'warehouses'));
    }

    public function adjust(Request $request): RedirectResponse
    {
        $this->authorize('inventory manage');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $stock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $validated['product_id'],
                        'warehouse_id' => $validated['warehouse_id']
                    ],
                    ['quantity' => 0, 'reserve_quantity' => 0]
                );

                $oldQty = $stock->quantity;
                $newQty = 0;
                $diff = 0;

                if ($validated['type'] === 'add') {
                    $newQty = $oldQty + $validated['quantity'];
                    $diff = $validated['quantity'];
                } elseif ($validated['type'] === 'subtract') {
                    $newQty = max(0, $oldQty - $validated['quantity']);
                    $diff = -$validated['quantity'];
                } else { // set
                    $newQty = $validated['quantity'];
                    $diff = $newQty - $oldQty;
                }

                $stock->update(['quantity' => $newQty]);

                InventoryMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'adjustment',
                    'quantity' => $diff,
                    'reason' => $validated['reason'],
                    'user_id' => auth()->id(),
                ]);

                // Sync denormalized stock
                $product = Product::find($validated['product_id']);
                $totalStock = InventoryStock::where('product_id', $product->id)->sum('quantity');
                $product->update(['stock_on_hand' => $totalStock]);
            });

            return back()->with('success', 'Inventory adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to adjust inventory: ' . $e->getMessage());
        }
    }
}
