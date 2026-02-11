<?php

use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\Warehouse;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = Product::where('is_active', true)->get();
$warehouses = Warehouse::all();

echo "Total Products: " . $products->count() . "\n";
echo "Total Warehouses: " . $warehouses->count() . "\n";

$productsWithoutStock = [];
$productsWithPartialStock = [];

foreach ($products as $product) {
    // Check if product has ANY stock records
    if ($product->stocks->isEmpty()) {
        $productsWithoutStock[] = $product->id;
        continue;
    }

    // Check coverage for ALL warehouses
    $stockWarehouseIds = $product->stocks->pluck('warehouse_id')->toArray();
    $missingWarehouses = [];

    foreach ($warehouses as $w) {
        if (!in_array($w->id, $stockWarehouseIds)) {
            $missingWarehouses[] = $w->id;
        }
    }

    if (!empty($missingWarehouses)) {
        $productsWithPartialStock[] = [
            'id' => $product->id,
            'name' => $product->name,
            'missing_warehouses' => $missingWarehouses
        ];
    }
}

echo "Products with NO stock records: " . count($productsWithoutStock) . "\n";
if (count($productsWithoutStock) > 0) {
    echo "  IDs: " . implode(', ', array_slice($productsWithoutStock, 0, 20)) . "\n";
}

echo "Products with PARTIAL stock records: " . count($productsWithPartialStock) . "\n";
if (count($productsWithPartialStock) > 0) {
    foreach (array_slice($productsWithPartialStock, 0, 5) as $p) {
        echo "  ID {$p['id']} ({$p['name']}) missing in Warehouse IDs: " . implode(', ', $p['missing_warehouses']) . "\n";
    }
}
