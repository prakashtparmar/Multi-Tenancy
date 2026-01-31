<?php

use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Order;
use App\Models\InventoryStock;
use App\Services\OrderService;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Starting Order Lifecycle Test ---\n";

// 1. Setup Data
echo "\n[1] Setting up Test Data...\n";
$warehouse = Warehouse::firstOrCreate(['code' => 'TEST-01'], ['name' => 'Test Warehouse']);
$customer = Customer::firstOrCreate(['email' => 'test@example.com'], [
    'first_name' => 'John', 'last_name' => 'Doe', 'mobile' => '1234567890'
]);

// Create Product with Stock
$product = Product::updateOrCreate(['sku' => 'TEST-PROD-01'], [
    'name' => 'Test Widget',
    'slug' => 'test-widget-01',
    'price' => 100.00,
    'unit_type' => 'pcs',
    'is_active' => true,
    'manage_stock' => true,
]);

// Reset Stock
InventoryStock::updateOrCreate(
    ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
    ['quantity' => 100, 'reserve_quantity' => 0]
);

echo "Initial Stock: " . $product->fresh()->stocks()->sum('quantity') . " (Reserved: " . $product->fresh()->stocks()->sum('reserve_quantity') . ")\n";

// 2. Place Order (Simulate Controller Store)
echo "\n[2] Placing Order (Pending)...\n";
$orderData = [
    'customer_id' => $customer->id,
    'warehouse_id' => $warehouse->id,
    'order_number' => 'ORD-' . time(),
    'items' => [
        ['product_id' => $product->id, 'quantity' => 10, 'price' => 100.00]
    ]
];

// Logic mimics OrderController@store
$order = Order::create([
    'customer_id' => $orderData['customer_id'],
    'warehouse_id' => $orderData['warehouse_id'],
    'order_number' => $orderData['order_number'],
    'status' => 'pending',
    'placed_at' => now(),
    'grand_total' => 1000.00,
]);

foreach ($orderData['items'] as $item) {
    $order->items()->create([
        'product_name' => $product->name,
        'sku' => $product->sku,
        'product_id' => $item['product_id'],
        'quantity' => $item['quantity'],
        'unit_price' => $item['price'],
        'total_price' => $item['quantity'] * $item['price'],
    ]);
}

$stock = InventoryStock::where('product_id', $product->id)->first();
echo "Order Status: {$order->fresh()->status}\n";
echo "Stock: {$stock->fresh()->quantity}, Reserved: {$stock->fresh()->reserve_quantity}\n";

if ($stock->fresh()->reserve_quantity != 0) {
    echo "FAIL: Stock should not be reserved yet!\n";
    exit(1);
}

// 3. Confirm Order (Reserve Stock)
echo "\n[3] Confirming Order (Processing)...\n";
$service = app(OrderService::class);
$service->confirmOrder($order);

$stock = $stock->fresh();
echo "Order Status: {$order->fresh()->status}\n";
echo "Stock: {$stock->quantity}, Reserved: {$stock->reserve_quantity}\n";

if ($stock->reserve_quantity != 10) {
    echo "FAIL: Stock should be reserved (10)!\n";
    exit(1);
}

// 4. Ship Order (Deduct Stock)
echo "\n[4] Shipping Order...\n";
$service->shipOrder($order, 'TRK-123456');

$stock = $stock->fresh();
echo "Order Status: {$order->fresh()->status}, Shipping Status: {$order->fresh()->shipping_status}\n";
echo "Stock: {$stock->quantity}, Reserved: {$stock->reserve_quantity}\n";

if ($stock->quantity != 90 || $stock->reserve_quantity != 0) {
    echo "FAIL: Stock should be 90 and Reserved 0!\n";
    exit(1);
}

// 5. Deliver Order
echo "\n[5] Delivering Order...\n";
$service->deliverOrder($order);
echo "Order Status: {$order->fresh()->status}, Shipping Status: {$order->fresh()->shipping_status}\n";

// 6. Test Cancellation Logic
echo "\n[6] Testing Cancellation Flow...\n";
// Create another order to cancel
$order2 = Order::create([
    'customer_id' => $customer->id,
    'warehouse_id' => $warehouse->id,
    'order_number' => 'ORD-CANCEL-' . time(),
    'status' => 'pending',
    'placed_at' => now(),
    'grand_total' => 500.00,
]);
$order2->items()->create([
    'product_name' => $product->name, 'sku' => $product->sku, 'product_id' => $product->id,
    'quantity' => 5, 'unit_price' => 100.00, 'total_price' => 500.00
]);

echo "Confirming Order 2...\n";
$service->confirmOrder($order2);
$stock = $stock->fresh();
echo "Stock: {$stock->quantity}, Reserved: {$stock->reserve_quantity} (Should be 5)\n";

if ($stock->reserve_quantity != 5) die("FAIL: Reservation failed for Order 2\n");

echo "Cancelling Order 2...\n";
$service->cancelOrder($order2);
$stock = $stock->fresh();
echo "Order Status: {$order2->fresh()->status}\n";
echo "Stock: {$stock->quantity}, Reserved: {$stock->reserve_quantity} (Should be 0)\n";

if ($stock->reserve_quantity != 0) die("FAIL: Reservation not released on cancel!\n");

echo "\n--- SUCCESS: Full Lifecycle Verified ---\n";
