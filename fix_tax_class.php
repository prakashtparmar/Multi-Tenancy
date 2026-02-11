<?php

use App\Models\Product;
use App\Models\TaxClass;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find Standard Rate Class (18%)
$standardClass = TaxClass::where('name', 'Standard Rate')->first();

if (!$standardClass) {
    die("Standard Rate tax class not found!\n");
}

// Update Product 12 (Gray Paul) which was 5% but should be 18%
$product = Product::find(12);
if ($product) {
    echo "Updating Product {$product->id} ({$product->name}) from Tax Class {$product->tax_class_id} to {$standardClass->id} (Standard Rate 18%)\n";
    $product->update(['tax_class_id' => $standardClass->id]);
    echo "Done.\n";
} else {
    echo "Product 12 not found.\n";
}

// Also check for any other products with manual rate 18 but class != Standard
$mismatched = Product::where('tax_rate', 18)
    ->where('tax_class_id', '!=', $standardClass->id)
    ->get();

foreach ($mismatched as $p) {
    echo "Updating mismatched Product {$p->id} ({$p->name}) to Standard Rate 18%\n";
    $p->update(['tax_class_id' => $standardClass->id]);
}
