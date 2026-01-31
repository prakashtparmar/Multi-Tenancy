<?php

namespace App\Services;

use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    /**
     * Confirm an order and reserve stock.
     */
    public function confirmOrder(Order $order)
    {
        if ($order->status !== 'pending' && $order->status !== 'draft') {
            throw new Exception("Order status must be pending to confirm.");
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = InventoryStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate() // Lock to prevent race conditions
                    ->first();

                if (!$stock) {
                    throw new Exception("Stock record not found for Product ID: {$item->product_id} in Warehouse: {$order->warehouse_id}");
                }

                $available = $stock->quantity - $stock->reserve_quantity;

                if ($available < $item->quantity) {
                    throw new Exception("Insufficient stock for Product: {$item->product_name}. Requested: {$item->quantity}, Available: {$available}");
                }

                // Reserve the stock
                $stock->increment('reserve_quantity', $item->quantity);
            }

            $order->update(['status' => 'processing', 'payment_status' => 'unpaid']); // Or paid if immediate
            
            return $order;
        });
    }

    /**
     * Ship an order and deduct physical stock.
     */
    public function shipOrder(Order $order, $trackingNumber = null)
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

                // Deduct both physical quantity and reservation
                $stock->decrement('quantity', $item->quantity);
                $stock->decrement('reserve_quantity', $item->quantity);

                // Log Movement
                InventoryMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'sale',
                    'quantity' => -$item->quantity,
                    'reference_id' => $order->id,
                    'reason' => "Order #{$order->order_number} shipped",
                    'user_id' => auth()->id(),
                ]);
            }

            $order->update([
                'status' => 'shipped',
                'shipping_status' => 'shipped',
            ]);

            // Create shipment record (simplified)
            $order->shipments()->create([
                'warehouse_id' => $order->warehouse_id,
                'tracking_number' => $trackingNumber,
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * Deliver an order.
     */
    public function deliverOrder(Order $order)
    {
         if ($order->status !== 'shipped') {
            throw new Exception("Order must be shipped to be delivered.");
        }

        $order->update([
            'status' => 'completed',
            'shipping_status' => 'delivered',
        ]);
        
        // Update shipment
        $order->shipments()->where('status', 'shipped')->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return $order;
    }

    /**
     * Cancel an order and release reservation.
     */
    public function cancelOrder(Order $order)
    {
        if (in_array($order->status, ['shipped', 'completed', 'cancelled'])) {
            throw new Exception("Cannot cancel order in status: {$order->status}");
        }

        return DB::transaction(function () use ($order) {
            // If order was processing, we likely reserved stock. Release it.
            // If it was pending, maybe we didn't reserve yet?
            // For strict mode, let's assume 'processing' implies reservation. 
            // 'pending' might not have reservation if we reserve on confirm.

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
            return $order;
        });
    }
}
