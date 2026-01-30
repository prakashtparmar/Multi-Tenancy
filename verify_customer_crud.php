<?php

use App\Models\Tenant;
use App\Models\Customer;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Customer CRUD Verification...\n";

// 1. Initialize Tenant
$tenant = Tenant::firstOrCreate(['id' => 'test_tenant']);
tenancy()->initialize($tenant);
echo "Initialized tenant 'test_tenant'.\n";

// 2. Cleanup & Create Customer
echo "Cleaning up old data...\n";
Customer::where('mobile', '9876543210')->forceDelete();

echo "Creating customer...\n";
$customerData = [
    'customer_code' => 'CUST-TEST-001-' . time(),
    'first_name' => 'John',
    'last_name' => 'Doe',
    'mobile' => '9876543210',
    'email' => 'john.doe@example.com',
    'type' => 'farmer',
    'category' => 'individual',
    'village' => 'Green Valley',
    'district' => 'AgriDistrict',
    'land_area' => 5.5,
    'crops' => [['name' => 'Wheat', 'season' => 'Rabi'], ['name' => 'Rice', 'season' => 'Kharif']],
];

try {
    $customer = Customer::create($customerData);
    echo "✅ Customer created successfully (ID: {$customer->id})\n";
} catch (\Exception $e) {
    echo "❌ Customer creation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Read Customer
$fetched = Customer::find($customer->id);
if ($fetched && $fetched->first_name === 'John' && is_array($fetched->crops)) {
    echo "✅ Customer read successfully. Crops data: " . json_encode($fetched->crops) . "\n";
} else {
    echo "❌ Customer read failed or data mismatch.\n";
    echo "   - fetched: " . ($fetched ? 'YES' : 'NO') . "\n";
    echo "   - name match: " . (($fetched->first_name === 'John') ? 'YES' : 'NO (' . $fetched->first_name . ')') . "\n";
    echo "   - crops is array: " . (is_array($fetched->crops) ? 'YES' : 'NO (' . gettype($fetched->crops) . ')') . "\n";
    echo "   - crops value: " . json_encode($fetched->crops) . "\n";
}

// 4. Update Customer
echo "Updating customer...\n";
$fetched->update(['first_name' => 'Jane']);
if ($fetched->fresh()->first_name === 'Jane') {
    echo "✅ Customer updated successfully.\n";
} else {
    echo "❌ Customer update failed.\n";
}

// 5. Delete Customer
echo "Deleting customer...\n";
$fetched->delete();
if (Customer::find($customer->id) === null) {
    echo "✅ Customer deleted successfully (Soft Deleted).\n";
} else {
    echo "❌ Customer delete failed.\n";
}

// Check Soft Delete
if (Customer::withTrashed()->find($customer->id)) {
    echo "✅ Customer exists in trash (Soft Delete verified).\n";
} else {
    echo "❌ Customer missing from trash.\n";
}

echo "Verification Complete.\n";
