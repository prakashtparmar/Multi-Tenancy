<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockTransferController extends Controller
{
    public function index()
    {
        // Simple view to list recent transfers or show transfer form
        $warehouses = Warehouse::where('is_active', true)->get();
        // Limit products for performance, ideally use AJAX search
        $products = Product::where('is_active', true)->limit(50)->get();

        return view('central.stock_transfers.index', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $sourceWarehouseId = $validated['source_warehouse_id'];
                $destinationWarehouseId = $validated['destination_warehouse_id'];
                $productId = $validated['product_id'];
                $quantity = (float) $validated['quantity'];
                $reason = $validated['reason'] ?? 'Stock Transfer';

                // Lock Source Stock
                $sourceStock = InventoryStock::where('warehouse_id', $sourceWarehouseId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$sourceStock) {
                    throw new Exception("Stock record not found in source warehouse.");
                }

                $available = $sourceStock->quantity - $sourceStock->reserve_quantity;

                if ($available < $quantity) {
                    throw new Exception("Insufficient available stock in source warehouse. Available: {$available}, Requested: {$quantity}");
                }

                // Decrement Source
                $sourceStock->decrement('quantity', $quantity);

                // Inventory Movement (Out)
                $transferRef = 'TR-' . uniqid();

                InventoryMovement::create([
                    'stock_id' => $sourceStock->id,
                    'type' => 'transfer_out',
                    'quantity' => -$quantity,
                    'reference_id' => null,
                    'reason' => "Transfer to Warehouse #{$destinationWarehouseId} ({$transferRef}) - " . $reason,
                    'user_id' => Auth::id(),
                ]);

                // Handle Destination Stock
                $destStock = InventoryStock::firstOrCreate(
                    [
                        'warehouse_id' => $destinationWarehouseId,
                        'product_id' => $productId,
                    ],
                    [
                        'quantity' => 0,
                        'reserve_quantity' => 0,
                    ]
                );

                // Lock Destination/Increment
                $destStock->increment('quantity', $quantity);

                // Inventory Movement (In)
                InventoryMovement::create([
                    'stock_id' => $destStock->id,
                    'type' => 'transfer_in',
                    'quantity' => $quantity,
                    'reference_id' => null,
                    'reason' => "Transfer from Warehouse #{$sourceWarehouseId} ({$transferRef}) - " . $reason,
                    'user_id' => Auth::id(),
                ]);
            });

            return redirect()->back()->with('success', 'Stock transferred successfully.');

        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}
