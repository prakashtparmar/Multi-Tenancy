<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\Category;
use App\Models\Brand;
use App\Models\TaxClass;

// Set tenant context (assuming first tenant for test)
$tenant = Tenant::first();
if (!$tenant) {
    echo "No tenant found.\n";
    exit(1);
}
tenancy()->initialize($tenant);

echo "Tenant initialized: {$tenant->id}\n";

// 1. Setup Data
$warehouseA = Warehouse::firstOrCreate(['code' => 'TEST-WH-A'], ['name' => 'Test Warehouse A', 'is_active' => true]);
$warehouseB = Warehouse::firstOrCreate(['code' => 'TEST-WH-B'], ['name' => 'Test Warehouse B', 'is_active' => true]);

if (!Schema::hasTable('tax_classes')) {
    echo "DEBUG: Creating tax_classes table...\n";
    Schema::create('tax_classes', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->decimal('rate', 8, 2)->default(0); // Assuming rate exists based on usage
        $table->timestamps();
        $table->softDeletes();
    });
}


$category = Category::firstOrCreate(['name' => 'Test Category'], ['is_active' => true, 'slug' => 'test-category']);
$brand = Brand::firstOrCreate(['name' => 'Test Brand'], ['is_active' => true]);
$taxClass = TaxClass::firstOrCreate(['name' => 'Test Tax'], ['rate' => 10, 'slug' => 'test-tax']);

$product = Product::first();

if (!$product) {
    echo "DEBUG: Current Database: " . DB::connection()->getDatabaseName() . "\n";
    try {
        $triggers = DB::select("SHOW TRIGGERS LIKE 'products'");
        echo "DEBUG: Triggers on products table: " . count($triggers) . "\n";
        foreach ($triggers as $t) {
            echo " - Trigger: {$t->Trigger} \n";
        }
    } catch (\Exception $e) {
        echo "DEBUG: Failed to list triggers: " . $e->getMessage() . "\n";
    }

    echo "DEBUG: No product found. Creating via DB::table...\n";
    try {
        $productId = DB::table('products')->insertGetId([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-' . uniqid(),
            'price' => 10.00,
            'cost_price' => 5.00,
            'stock_on_hand' => 0,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_type' => 'pcs',
            'slug' => 'test-product-' . uniqid(),
            'description' => 'Test',
            'is_active' => 1,
            'is_taxable' => 1,
            'manage_stock' => 1,
            'min_order_qty' => 1,
            'reorder_level' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "DEBUG: Product created with ID: $productId\n";
        $product = Product::find($productId);
    } catch (\Exception $e) {
        echo "DEBUG: Failed to create product: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "DEBUG: Product found: {$product->id}\n";
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
            'reason' => "Test Transfer",
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
            'reason' => "Test Transfer",
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
    echo "TEST PASSED\n";
} else {
    echo "TEST FAILED\n";
}
