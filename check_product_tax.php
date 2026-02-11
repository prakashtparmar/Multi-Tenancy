<?php

use App\Models\Product;
use App\Models\TaxClass;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check products with tax info
$products = Product::where('is_active', true)->with(['taxClass.rates'])->get();

echo "Product ID | Name | Tax Rate (Manual) | Tax Class | Class Rate\n";
echo "-------------------------------------------------------------\n";

foreach ($products as $product) {

    $classRate = '-';
    $className = '-';

    if ($product->taxClass) {
        $className = $product->taxClass->name;
        $rateObj = $product->taxClass->rates->first();
        if ($rateObj) {
            $classRate = $rateObj->rate . '%';
        }
    }

    echo "{$product->id} | {$product->name} | {$product->tax_rate}% | {$className} | {$classRate}\n";
}
