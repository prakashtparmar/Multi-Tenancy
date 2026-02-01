<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Confirm an order and reserve stock.
     */
    public function confirmOrder(Order $order): Order
    {
        if (!in_array($order->status, ['pending', 'draft', 'scheduled'])) {
            throw new Exception("Order status must be pending or scheduled to confirm.");
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = InventoryStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw new Exception("Stock record not found for Product ID: {$item->product_id} in Warehouse: {$order->warehouse_id}");
                }

                $available = $stock->quantity - $stock->reserve_quantity;

                if ($available < $item->quantity) {
                    throw new Exception("Insufficient stock for Product ID: {$item->product_id}. Requested: {$item->quantity}, Available: {$available}");
                }

                $stock->increment('reserve_quantity', $item->quantity);
            }

            $order->update([
                'status' => 'processing',
                'payment_status' => 'unpaid'
            ]);
            
            return $order->fresh();
        });
    }

    /**
     * Ship an order and deduct physical stock.
     */
    public function shipOrder(Order $order, ?string $trackingNumber = null): Order
    {
        if ($order->status !== 'processing') {
            throw new Exception("Order must be processing to be shipped.");
        }

        return DB::transaction(function () use ($order, $trackingNumber) {
            foreach ($order->items as $item) {
                $stock = InventoryStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
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
                }
            }

            $order->update([
                'status' => 'shipped',
                'shipping_status' => 'shipped',
            ]);

            $order->shipments()->create([
                'warehouse_id' => $order->warehouse_id,
                'tracking_number' => $trackingNumber,
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Deliver an order.
     */
    public function deliverOrder(Order $order): Order
    {
         if ($order->status !== 'shipped') {
            throw new Exception("Order must be shipped to be delivered.");
        }

        $order->update([
            'status' => 'completed',
            'shipping_status' => 'delivered',
        ]);
        
        $order->shipments()->where('status', 'shipped')->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return $order->fresh();
    }

    /**
     * Cancel an order and release reservation.
     */
    public function cancelOrder(Order $order): Order
    {
        if (in_array($order->status, ['shipped', 'completed', 'cancelled'])) {
            throw new Exception("Cannot cancel order in status: {$order->status}");
        }

        return DB::transaction(function () use ($order) {
            if ($order->status === 'processing') {
                foreach ($order->items as $item) {
                     $stock = InventoryStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $order->warehouse_id)
                        ->first();
                    
                    if ($stock) {
                        $stock->decrement('reserve_quantity', $item->quantity);
                    }
                }
            }

            $order->update(['status' => 'cancelled']);
            return $order->fresh();
        });
    }
}
