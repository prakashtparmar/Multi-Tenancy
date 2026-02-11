<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Product;
use App\Notifications\InventoryUpdatedNotification;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('inventory manage');

        $query = \App\Models\Product::with(['stocks.warehouse', 'category', 'stocks']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Special filters
        if ($request->input('status') === 'low_stock') {
            $query->where('stock_on_hand', '<=', 10)->where('stock_on_hand', '>', 0);
        } elseif ($request->input('status') === 'out_of_stock') {
            $query->where('stock_on_hand', '<=', 0);
        }

        $products = $query->paginate(15)->withQueryString();
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();

        return view('central.inventory.index', compact('products', 'warehouses'));
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
            $result = DB::transaction(function () use ($validated) {
                // Use FQCN to avoid namespace resolution errors
                $stock = \App\Models\InventoryStock::firstOrCreate(
                    ['product_id' => $validated['product_id'], 'warehouse_id' => $validated['warehouse_id']],
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

                \App\Models\InventoryMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'adjustment',
                    'quantity' => $diff,
                    'reason' => $validated['reason'],
                    'user_id' => auth()->id(),
                ]);

                $product = \App\Models\Product::find($validated['product_id']);
                $oldTotal = $product->stock_on_hand;
                $totalStock = \App\Models\InventoryStock::where('product_id', $product->id)->sum('quantity');

                $product->update(['stock_on_hand' => $totalStock]);

                return [
                    'message' => "Stock updated successfully. Total is now {$totalStock} (was {$oldTotal}).",
                    'old_total' => $oldTotal,
                    'new_total' => $totalStock
                ];
            });

            // Send Notification
            $product = Product::find($validated['product_id']);
            $user = auth()->user();
            $user->notify(new InventoryUpdatedNotification(
                $product,
                (float) $validated['quantity'],
                $validated['type'],
                $validated['reason'],
                (float) $result['old_total'],
                (float) $result['new_total']
            ));

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to adjust inventory: ' . $e->getMessage());
        }
    }
}
