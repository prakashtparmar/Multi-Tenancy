<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\InventoryStock;
use App\Models\Customer;
use App\Models\Order;

class DemoAgricultureSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Seeding Agriculture Demo Data (INR)...\n";

        /* -------------------------------------------------
         | 1. Categories
         |--------------------------------------------------*/
        $catFruits  = Category::firstOrCreate(['slug' => 'fruits'], ['name' => 'Fresh Fruits', 'is_active' => true]);
        $catVeg     = Category::firstOrCreate(['slug' => 'vegetables'], ['name' => 'Vegetables', 'is_active' => true]);
        $catGrains  = Category::firstOrCreate(['slug' => 'grains'], ['name' => 'Grains & Cereals', 'is_active' => true]);

        /* -------------------------------------------------
         | 2. Brands
         |--------------------------------------------------*/
        $brandOrganic = Brand::firstOrCreate(['slug' => 'organic-co'], ['name' => 'The Organic Co.', 'is_active' => true]);
        $brandFarm    = Brand::firstOrCreate(['slug' => 'valley-farms'], ['name' => 'Valley Farms', 'is_active' => true]);

        /* -------------------------------------------------
         | 3. Warehouses
         |--------------------------------------------------*/
        $mainWarehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            ['name' => 'Central Distribution Hub', 'address' => 'Delhi, India', 'is_active' => true]
        );

        $westWarehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-WEST'],
            ['name' => 'West Cold Storage', 'address' => 'Mumbai, India', 'is_active' => true]
        );

        /* -------------------------------------------------
         | 4. Supplier
         |--------------------------------------------------*/
        Supplier::firstOrCreate(['email' => 'farmer@demo.test'], [
            'company_name' => 'Demo Green Farms',
            'contact_name' => 'Ramesh Patel',
            'phone' => '9999999999',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        /* -------------------------------------------------
         | 5. Products (10 items)
         |--------------------------------------------------*/
        $productData = [
            ['FRU-APP-01', 'Apple', 180, $catFruits, $brandFarm, 'Himachal'],
            ['FRU-BAN-01', 'Banana', 40,  $catFruits, $brandFarm, 'Kerala'],
            ['FRU-ORG-01', 'Orange', 70,  $catFruits, $brandFarm, 'Nagpur'],
            ['VEG-POT-01', 'Potato', 30,  $catVeg,    $brandOrganic, 'Agra'],
            ['VEG-TOM-01', 'Tomato', 35,  $catVeg,    $brandOrganic, 'Nashik'],
            ['VEG-CAR-01', 'Carrot', 60,  $catVeg,    $brandOrganic, 'Ooty'],
            ['GRN-WHT-01', 'Wheat',  45,  $catGrains, $brandFarm, 'Punjab'],
            ['GRN-RIC-01', 'Rice',   55,  $catGrains, $brandFarm, 'West Bengal'],
            ['GRN-MLT-01', 'Millet', 65,  $catGrains, $brandOrganic, 'Karnataka'],
            ['GRN-OAT-01', 'Oats',   90,  $catGrains, $brandOrganic, 'Haryana'],
        ];

        $products = [];

        foreach ($productData as [$sku, $name, $price, $category, $brand, $origin]) {
            $products[] = Product::firstOrCreate(['sku' => $sku], [
                'name' => $name,
                'slug' => str($name)->slug(),
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'description' => "{$name} fresh from farm",
                'price' => $price,
                'unit_type' => 'kg',
                'harvest_date' => now()->subDays(rand(2, 10)),
                'expiry_date' => now()->addDays(rand(15, 60)),
                'origin' => $origin . ', India',
                'is_organic' => $brand->slug === 'organic-co',
                'manage_stock' => true,
                'is_active' => true,
            ]);
        }

        /* -------------------------------------------------
         | 6. Inventory (10 qty per product per warehouse)
         |--------------------------------------------------*/
        foreach ($products as $product) {
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $mainWarehouse->id, 'product_id' => $product->id],
                ['quantity' => 10, 'reserve_quantity' => 0]
            );

            InventoryStock::updateOrCreate(
                ['warehouse_id' => $westWarehouse->id, 'product_id' => $product->id],
                ['quantity' => 10, 'reserve_quantity' => 0]
            );

            $product->refreshStockOnHand();
        }

        /* -------------------------------------------------
         | 7. Customer
         |--------------------------------------------------*/
        $customer = Customer::firstOrCreate(['email' => 'buyer@demo.test'], [
            'first_name' => 'Demo',
            'last_name' => 'Buyer',
            'mobile' => '8888888888',
            'customer_code' => 'CUST-DEMO',
        ]);

        /* -------------------------------------------------
         | 8. Sample Orders (SAFE: No inventory touch)
         |--------------------------------------------------*/
        Order::firstOrCreate(['order_number' => 'ORD-DEMO-001'], [
            'customer_id' => $customer->id,
            'warehouse_id' => $mainWarehouse->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'shipping_status' => 'pending',
            'total_amount' => 180,
            'grand_total' => 180,
            'placed_at' => now()->subHour(),
        ]);

        echo "✅ Agriculture Demo Data Seeded (10 products, 10 qty each)!\n";
    }
}
