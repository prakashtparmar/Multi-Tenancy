<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\InventoryStock;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Services\OrderService;

class DemoAgricultureSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Seeding Agriculture Demo Data (INR)...\n";

        // 0. Cleanup Old Demo Data
        $demoOrderNumbers = ['ORD-DEMO-001', 'ORD-DEMO-002', 'ORD-DEMO-003'];
        $orders = Order::withTrashed()->whereIn('order_number', $demoOrderNumbers)->get();
        foreach ($orders as $o) {
            $o->shipments()->delete();
            $o->items()->forceDelete(); 
            $o->forceDelete();
        }

        // 1. Categories (Specific to Produce, separate from General Categories)
        $catFruits = Category::firstOrCreate(['slug' => 'fruits'], ['name' => 'Fresh Fruits', 'image' => null, 'is_active' => true]);
        $catVeg = Category::firstOrCreate(['slug' => 'vegetables'], ['name' => 'Vegetables', 'image' => null, 'is_active' => true]);
        $catGrains = Category::firstOrCreate(['slug' => 'grains'], ['name' => 'Grains & Cereals', 'image' => null, 'is_active' => true]);

        // 2. Brands
        $brandOrganic = Brand::firstOrCreate(['slug' => 'organic-co'], ['name' => 'The Organic Co.', 'is_active' => true]);
        $brandFarm = Brand::firstOrCreate(['slug' => 'valley-farms'], ['name' => 'Valley Farms', 'is_active' => true]);
        $brandAgro = Brand::firstOrCreate(['slug' => 'agro-tech'], ['name' => 'Agro Tech', 'is_active' => true]);

        // 3. Warehouses
        $mainWarehouse = Warehouse::firstOrCreate(['code' => 'WH-MAIN'], [
            'name' => 'Central Distribution Hub', 
            'address' => '123 Agri Lane, Springfield',
            'is_active' => true
        ]);
        $westWarehouse = Warehouse::firstOrCreate(['code' => 'WH-WEST'], [
            'name' => 'West Coast Cold Storage', 
            'address' => '456 Coast Blvd, California',
            'is_active' => true
        ]);

        // 4. Suppliers
        $supplier1 = Supplier::firstOrCreate(['email' => 'contact@greenacres.test'], [
            'company_name' => 'Green Acres Farm',
            'contact_name' => 'John Green',
            'phone' => '9898989898',
            'farm_size' => 500.0,
            'primary_crop' => 'Apples',
            'verification_status' => 'verified',
            'is_active' => true
        ]);

        // 5. Products
        $products = [];
        
        // Product 1: Apples (Price Rs 180/kg)
        $products[] = Product::firstOrCreate(['sku' => 'FRU-APP-001'], [
            'name' => 'Fuji Apples (Premium)',
            'slug' => 'fuji-apples-premium',
            'category_id' => $catFruits->id,
            'brand_id' => $brandFarm->id,
            'description' => 'Crisp and sweet Fuji apples directly from the orchard.',
            'price' => 180.00, 
            'unit_type' => 'kg',
            'harvest_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(20),
            'origin' => 'Himachal Pradesh, India',
            'is_organic' => false,
            'manage_stock' => true,
        ]);

        // Product 2: Organic Carrots (Price Rs 60/kg)
        $products[] = Product::firstOrCreate(['sku' => 'VEG-CAR-001'], [
            'name' => 'Organic Carrots',
            'slug' => 'organic-carrots',
            'category_id' => $catVeg->id,
            'brand_id' => $brandOrganic->id,
            'description' => 'Crunchy organic carrots, rich in beta-carotene.',
            'price' => 60.00,
            'unit_type' => 'kg',
            'harvest_date' => now()->subDays(2),
            'expiry_date' => now()->addDays(14),
            'origin' => 'Ooty, India',
            'is_organic' => true,
            'certification_number' => 'ORG-IN-8852',
            'manage_stock' => true,
        ]);

        // Product 3: Wheat (Price Rs 45/kg)
        $products[] = Product::firstOrCreate(['sku' => 'GRN-WHT-001'], [
            'name' => 'Whole Wheat Grain',
            'slug' => 'whole-wheat-grain',
            'category_id' => $catGrains->id,
            'brand_id' => $brandFarm->id,
            'description' => 'High quality whole wheat grains for milling.',
            'price' => 45.00,
            'unit_type' => 'kg',
            'harvest_date' => now()->subMonths(1),
            'expiry_date' => now()->addMonths(6),
            'origin' => 'Punjab, India',
            'is_organic' => false,
            'manage_stock' => true,
        ]);

        // 6. Inventory (Zero stock as requested)
        foreach ($products as $product) {
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $mainWarehouse->id, 'product_id' => $product->id],
                ['quantity' => 0, 'reserve_quantity' => 0]
            );
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $westWarehouse->id, 'product_id' => $product->id],
                ['quantity' => 0, 'reserve_quantity' => 0]
            );
        }

        // 7. Customers
        $customer = Customer::firstOrCreate(['email' => 'alice@buyer.test'], [
            'first_name' => 'Alice',
            'last_name' => 'Buyer',
            'mobile' => '9876543210', 
            'customer_code' => 'CUST-DEMO-01',
        ]);

        // 8. Orders (Demo Lifecycle)
        $orderService = app(OrderService::class);

        // Order 1: Pending (10kg Apples @ 180 = 1800)
        $order1 = Order::create([
            'order_number' => 'ORD-DEMO-001',
            'customer_id' => $customer->id,
            'warehouse_id' => $mainWarehouse->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'shipping_status' => 'pending',
            'total_amount' => 1800.00,
            'grand_total' => 1800.00,
            'placed_at' => now()->subHours(2),
        ]);
        $order1->items()->create([
            'product_id' => $products[0]->id,
            'product_name' => $products[0]->name,
            'sku' => $products[0]->sku,
            'quantity' => 10,
            'unit_price' => 180.00,
            'total_price' => 1800.00,
        ]);

        // Order 2: Processing (10kg Carrots @ 60 = 600)
        $order2 = Order::create([
            'order_number' => 'ORD-DEMO-002',
            'customer_id' => $customer->id,
            'warehouse_id' => $mainWarehouse->id,
            'status' => 'pending', 
            'payment_status' => 'paid',
            'shipping_status' => 'pending',
            'total_amount' => 600.00,
            'grand_total' => 600.00,
            'placed_at' => now()->subHours(5),
        ]);
        $order2->items()->create([
            'product_id' => $products[1]->id,
            'product_name' => $products[1]->name,
            'sku' => $products[1]->sku,
            'quantity' => 10,
            'unit_price' => 60.00,
            'total_price' => 600.00,
        ]);
        // $orderService->confirmOrder($order2); // Disabled: Needs stock

        // Order 3: Shipped (50kg Wheat @ 45 = 2250)
        $order3 = Order::create([
            'order_number' => 'ORD-DEMO-003',
            'customer_id' => $customer->id,
            'warehouse_id' => $westWarehouse->id,
            'status' => 'pending',
            'payment_status' => 'paid',
            'shipping_status' => 'pending',
            'total_amount' => 2250.00,
            'grand_total' => 2250.00,
            'placed_at' => now()->subDay(),
        ]);
        $order3->items()->create([
            'product_id' => $products[2]->id,
            'product_name' => $products[2]->name,
            'sku' => $products[2]->sku,
            'quantity' => 50,
            'unit_price' => 45.00,
            'total_price' => 2250.00,
        ]);
        // $orderService->confirmOrder($order3); // Disabled: Needs stock
        // $orderService->shipOrder($order3, 'TRK-FEDEX-999'); // Disabled: Needs confirmation first

        echo "✅ Demo Data Seeded Successfully (INR)!\n";
    }
}
