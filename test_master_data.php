<?php

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Starting Master Data Verification ---\n";

// 1. Test Product Creation with Agri Fields
echo "\n[1] Testing Product CRUD...\n";
$sku = 'AGRI-TEST-'.Str::random(4);
try {
    $product = Product::create([
        'name' => 'Organic Apples',
        'slug' => Str::slug('Organic Apples '.$sku),
        'sku' => $sku,
        'price' => 50.00,
        'unit_type' => 'kg',
        'harvest_date' => now()->subDays(10),
        'expiry_date' => now()->addDays(20),
        'origin' => 'Washington State',
        'is_organic' => true,
        'certification_number' => 'ORG-999',
        'is_active' => true,
        'manage_stock' => true
    ]);
    echo "Product Created: {$product->name} (Organic: {$product->is_organic})\n";
} catch (\Exception $e) {
    echo "FAIL: Product Creation - " . $e->getMessage() . "\n";
    exit(1);
}

// Update
try {
    $product->update(['price' => 55.00, 'origin' => 'Oregon State']);
    echo "Product Updated: Price {$product->price}, Origin {$product->origin}\n";
} catch (\Exception $e) {
    echo "FAIL: Product Update - " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test Supplier Creation with Agri Fields
echo "\n[2] Testing Supplier CRUD...\n";
try {
    $supplier = Supplier::create([
        'company_name' => 'Green Farms Ltd',
        'contact_name' => 'Jane Farmer',
        'email' => 'jane@greenfarms.test',
        'phone' => '987-654-3210',
        'farm_size' => 120.5, // Acres
        'primary_crop' => 'Apples',
        'verification_status' => 'verified',
        'is_active' => true
    ]);
    echo "Supplier Created: {$supplier->company_name} (Crop: {$supplier->primary_crop})\n";
} catch (\Exception $e) {
    echo "FAIL: Supplier Creation - " . $e->getMessage() . "\n";
    exit(1);
}

// Update
try {
    $supplier->update(['farm_size' => 150.0]);
    echo "Supplier Updated: Farm Size {$supplier->farm_size}\n";
} catch (\Exception $e) {
    echo "FAIL: Supplier Update - " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n--- SUCCESS: Master Data Modules Verified ---\n";
