<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\Category;
use App\Models\Brand;
use App\Models\TaxClass;

echo "Running in Central Context...\n";

// 1. Setup Data (Central DB)
$warehouseA = Warehouse::firstOrCreate(['code' => 'CENTRAL-WH-A'], ['name' => 'Central Warehouse A', 'is_active' => true]);
$warehouseB = Warehouse::firstOrCreate(['code' => 'CENTRAL-WH-B'], ['name' => 'Central Warehouse B', 'is_active' => true]);

$category = Category::firstOrCreate(['name' => 'Central Category'], ['is_active' => true, 'slug' => 'central-category']);
$brand = Brand::firstOrCreate(['name' => 'Central Brand'], ['is_active' => true]);
$taxClass = TaxClass::firstOrCreate(['name' => 'Central Tax'], ['rate' => 10, 'slug' => 'central-tax']);

$product = Product::first();

if (!$product) {
    echo "No product found. Creating one via DB::table...\n";
    $productId = DB::table('products')->insertGetId([
        'name' => 'Central Product',
        'sku' => 'CENTRAL-SKU-' . uniqid(),
        'price' => 10.00,
        'cost_price' => 5.00,
        'image_url' => '',
        'stock_on_hand' => 0,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'tax_class_id' => $taxClass->id,
        'unit_type' => 'pcs',
        'slug' => 'central-product-' . uniqid(),
        'description' => 'Test',
        'is_active' => 1,
        'is_taxable' => 1,
        'manage_stock' => 1,
        'min_order_qty' => 1,
        'reorder_level' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $product = Product::find($productId);
}

echo "Using Product: {$product->name} (ID: {$product->id})\n";

// Reset Stock for Test
InventoryStock::updateOrCreate(
    ['warehouse_id' => $warehouseA->id, 'product_id' => $product->id],
    ['quantity' => 100, 'reserve_quantity' => 0]
);
InventoryStock::updateOrCreate(
    ['warehouse_id' => $warehouseB->id, 'product_id' => $product->id],
    ['quantity' => 0, 'reserve_quantity' => 0]
);

echo "Initial Stock: WH-A: 100, WH-B: 0\n";

// 2. Perform Transfer Logic (Simulation of Controller Logic)
$transferQty = 25;

try {
    DB::transaction(function () use ($warehouseA, $warehouseB, $product, $transferQty) {

        $sourceStock = InventoryStock::where('warehouse_id', $warehouseA->id)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        // Decrement Source
        $sourceStock->decrement('quantity', $transferQty);

        // Movement Out
        InventoryMovement::create([
            'stock_id' => $sourceStock->id,
            'type' => 'transfer_out',
            'quantity' => -$transferQty,
            'reason' => "Central Test Transfer",
            'user_id' => 1,
        ]);

        // Destination
        $destStock = InventoryStock::firstOrCreate(
            ['warehouse_id' => $warehouseB->id, 'product_id' => $product->id],
            ['quantity' => 0, 'reserve_quantity' => 0]
        );
        $destStock->increment('quantity', $transferQty);

        // Movement In
        InventoryMovement::create([
            'stock_id' => $destStock->id,
            'type' => 'transfer_in',
            'quantity' => $transferQty,
            'reason' => "Central Test Transfer",
            'user_id' => 1,
        ]);

    });

    echo "Transfer successful.\n";

} catch (\Exception $e) {
    echo "Transfer failed: " . $e->getMessage() . "\n";
}

// 3. Verify
$stockA = InventoryStock::where('warehouse_id', $warehouseA->id)->where('product_id', $product->id)->first();
$stockB = InventoryStock::where('warehouse_id', $warehouseB->id)->where('product_id', $product->id)->first();

echo "Final Stock: WH-A: {$stockA->quantity}, WH-B: {$stockB->quantity}\n";

if ($stockA->quantity == 75 && $stockB->quantity == 25) {
    echo "CENTRAL TEST PASSED\n";
} else {
    echo "CENTRAL TEST FAILED\n";
}
