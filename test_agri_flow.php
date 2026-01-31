
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;

echo "Starting Agriculture Data Test...\n";

// 1. Setup Dependencies
$category = Category::firstOrCreate(['slug' => 'vegetables'], ['name' => 'Vegetables']);
$brand = Brand::firstOrCreate(['slug' => 'green-farms'], ['name' => 'Green Farms']);

echo "Category: {$category->name}\n";
echo "Brand: {$brand->name}\n";

// 2. Test Product Creation (Agri Fields)
echo "Creating Product...\n";
try {
    $product = Product::create([
        'name' => 'Organic Carrots',
        'slug' => 'organic-carrots-' . uniqid(),
        'sku' => 'ORG-CAR-001-' . uniqid(),
        'type' => 'simple',
        'price' => 2.50,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'description' => 'Fresh crisp carrots from the valley.',
        // Agri Fields
        'harvest_date' => now()->subDays(2),
        'expiry_date' => now()->addDays(14),
        'origin' => 'California Valley',
        'is_organic' => true,
        'certification_number' => 'ORG-12345678',
        'unit_type' => 'kg',
        'weight' => 1.0,
    ]);
    
    echo "Product Created: {$product->name} (ID: {$product->id})\n";
    echo " - Origin: {$product->origin}\n";
    echo " - Organic: " . ($product->is_organic ? 'Yes' : 'No') . "\n";
    echo " - Harvest: {$product->harvest_date->toDateString()}\n";

} catch (\Exception $e) {
    echo "ERROR Creating Product: " . $e->getMessage() . "\n";
}

// 3. Test Supplier Creation (Agri Profile)
echo "\nCreating Supplier...\n";
try {
    $supplier = Supplier::create([
        'company_name' => 'Valley Growers Co-op',
        'contact_name' => 'John Farmer',
        'email' => 'john@valleygrowers.test',
        'phone' => '555-0199',
        'currency' => 'USD',
        // Agri Fields
        'farm_size' => 500.50, // Acres
        'primary_crop' => 'Root Vegetables',
        'verification_status' => 'verified',
        'is_active' => true,
    ]);

    echo "Supplier Created: {$supplier->company_name} (ID: {$supplier->id})\n";
    echo " - Farm Size: {$supplier->farm_size} acres\n";
    echo " - Primary Crop: {$supplier->primary_crop}\n";
    echo " - Status: {$supplier->verification_status}\n";

} catch (\Exception $e) {
    echo "ERROR Creating Supplier: " . $e->getMessage() . "\n";
}

echo "\nTest Completed.\n";
