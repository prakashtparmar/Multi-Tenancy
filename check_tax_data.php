<?php

use App\Models\Product;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = Product::where('is_active', true)
    ->with(['taxClass.rates'])
    ->limit(5)
    ->get();

foreach ($products as $product) {
    echo "Product: {$product->name} (ID: {$product->id})\n";
    echo "  Tax Class ID: " . ($product->tax_class_id ?? 'NULL') . "\n";
    echo "  Manual Tax Rate: " . ($product->tax_rate ?? '0') . "\n";

    if ($product->taxClass) {
        echo "  Tax Class: {$product->taxClass->name}\n";
        foreach ($product->taxClass->rates as $rate) {
            echo "    Rate: {$rate->name} - {$rate->rate}%\n";
        }
    } else {
        echo "  No Tax Class Relation Found.\n";
    }
    echo "--------------------------------------------------\n";
}
