<?php

use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\Warehouse;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = Product::all();
$warehouses = Warehouse::all();

echo "Checking " . $products->count() . " products against " . $warehouses->count() . " warehouses.\n";

$fixedCount = 0;

foreach ($products as $product) {
    foreach ($warehouses as $w) {
        $stock = InventoryStock::where('product_id', $product->id)
            ->where('warehouse_id', $w->id)
            ->first();

        if (!$stock) {
            echo "Creating stock for Product ID {$product->id} in Warehouse {$w->id}\n";
            InventoryStock::create([
                'product_id' => $product->id,
                'warehouse_id' => $w->id,
                'quantity' => 0,
                'reserve_quantity' => 0,
            ]);
            $fixedCount++;
        }
    }
}

echo "Fixed $fixedCount missing stock records.\n";
