<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderService
{
    /**
     * Confirm order and reserve stock.
     * pending | draft | scheduled → processing
     */
    public function confirmOrder(Order $order): Order
    {
        if (!in_array($order->status, ['pending', 'draft', 'scheduled'])) {
            throw new Exception("Order cannot be confirmed from status: {$order->status}");
        }

        return DB::transaction(function () use ($order) {

            foreach ($order->items as $item) {
                // Use firstOrCreate to ensure stock record exists to correct "No query results" error
                // We use firstOrNew -> lock -> save to be safe with locking, or simply handle the null case.

                $stock = InventoryStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = InventoryStock::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $order->warehouse_id,
                        'quantity' => 0,
                        'reserve_quantity' => 0,
                    ]);
                }

                $available = $stock->quantity - $stock->reserve_quantity;

                if ($available < $item->quantity) {
                    throw new Exception(
                        "Insufficient stock for Product ID {$item->product_id}. 
                        Required: {$item->quantity}, Available: {$available}"
                    );
                }

                $stock->increment('reserve_quantity', $item->quantity);
                $item->product->refreshStockOnHand();
            }

            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'updated_by' => Auth::id(),
            ]);

            foreach ($order->items as $item) {
                $item->product->refreshStockOnHand();
            }

            return $order->fresh();
        });
    }

    /**
     * Ship order and deduct physical stock.
     * processing | ready_to_ship → shipped
     */
    public function shipOrder(
        Order $order,
        ?string $trackingNumber = null,
        ?string $carrier = null
    ): Order {
        if (!in_array($order->status, ['processing', 'ready_to_ship'])) {
            throw new Exception("Order cannot be shipped from status: {$order->status}");
        }

        return DB::transaction(function () use ($order, $trackingNumber, $carrier) {

            foreach ($order->items as $item) {
                $stock = InventoryStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($stock->reserve_quantity < $item->quantity) {
                    throw new Exception(
                        "Reserved stock mismatch for Product ID {$item->product_id}"
                    );
                }

                $stock->decrement('quantity', $item->quantity);
                $stock->decrement('reserve_quantity', $item->quantity);

                InventoryMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'sale',
                    'quantity' => -$item->quantity,
                    'reference_id' => $order->id,
                    'reason' => "Order #{$order->order_number} shipped",
                    'user_id' => Auth::id(),
                ]);

                $item->product->refreshStockOnHand();
            }

            $order->update([
                'status' => 'shipped',
                'shipping_status' => 'shipped',
                'updated_by' => Auth::id(),
            ]);

            // Create shipment record
            $order->shipments()->create([
                'warehouse_id' => $order->warehouse_id,
                'tracking_number' => $trackingNumber,
                'carrier' => $carrier,
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Deliver order.
     * shipped → completed
     */
    public function deliverOrder(Order $order): Order
    {
        if ($order->status !== 'shipped') {
            throw new Exception("Order must be shipped before delivery.");
        }

        return DB::transaction(function () use ($order) {

            $order->update([
                'status' => 'completed',
                'shipping_status' => 'delivered',
                'completed_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $order->shipments()
                ->where('status', 'shipped')
                ->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);

            return $order->fresh();
        });
    }

    /**
     * Release reserved stock without changing status.
     */
    public function releaseReserves(Order $order): void
    {
        foreach ($order->items as $item) {
            $stock = InventoryStock::where('product_id', $item->product_id)
                ->where('warehouse_id', $order->warehouse_id)
                ->lockForUpdate()
                ->first();

            if ($stock && $stock->reserve_quantity > 0) {
                // Determine how much to release: the item quantity, but cap it at current reserve
                $toRelease = min($item->quantity, $stock->reserve_quantity);
                $stock->decrement('reserve_quantity', $toRelease);
                $item->product->refreshStockOnHand();
            }
        }
    }

    /**
     * Apply reserved stock without changing status.
     */
    public function applyReserves(Order $order): void
    {
        foreach ($order->items as $item) {
            $stock = InventoryStock::where('product_id', $item->product_id)
                ->where('warehouse_id', $order->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = InventoryStock::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'quantity' => 0,
                    'reserve_quantity' => 0,
                ]);
            }

            $available = $stock->quantity - $stock->reserve_quantity;

            if ($available < $item->quantity) {
                throw new Exception(
                    "Insufficient stock for Product ID {$item->product_id}. 
                    Required: {$item->quantity}, Available: {$available}"
                );
            }

            $stock->increment('reserve_quantity', $item->quantity);
            $item->product->refreshStockOnHand();
        }
    }

    /**
     * Cancel order and release reserved stock.
     */
    public function cancelOrder(Order $order): Order
    {
        if (in_array($order->status, ['shipped', 'completed', 'cancelled'])) {
            throw new Exception("Order cannot be cancelled from status: {$order->status}");
        }

        return DB::transaction(function () use ($order) {

            if (in_array($order->status, ['confirmed', 'processing', 'ready_to_ship'])) {
                $this->releaseReserves($order);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            return $order->fresh();
        });
    }
}
