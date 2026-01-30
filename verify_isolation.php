<?php

use App\Models\Tenant;
use App\Models\Customer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Hybrid Multi-Tenancy Verification...\n";
echo "--------------------------------------------------\n";

// Helper to create a customer
function createCustomer($prefix, $suffix) {
    return Customer::firstOrCreate(
        ['mobile' => "90000000{$suffix}"],
        [
            'customer_code' => "{$prefix}-" . time() . "-{$suffix}",
            'first_name' => "{$prefix}",
            'last_name' => "User",
            'email' => "{$prefix}@example.com",
            'type' => 'farmer',
            'category' => 'individual',
        ]
    );
}

// 1. Central Context
echo "\n[1] Testing Central Context:\n";
tenancy()->end(); // Ensure we are in central
echo "    - Current Connection: " . DB::connection()->getName() . "\n";
echo "    - Creating Central Customer (C1)...\n";
try {
    $c1 = createCustomer('Central', '01');
    echo "    ✅ Created Central Customer: {$c1->first_name} (ID: {$c1->id})\n";
} catch (\Exception $e) {
    echo "    ❌ Failed to create Central customer: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Tenant A Context
echo "\n[2] Testing Tenant A Context:\n";
$tenantA = Tenant::firstOrCreate(['id' => 'tenant_a']);
echo "    - Initializing Tenant A...\n";
tenancy()->initialize($tenantA);
echo "    - Current Connection: " . DB::connection()->getName() . "\n";

// Check Visibility
$visibleC1 = Customer::where('customer_code', $c1->customer_code)->first();
if (!$visibleC1) {
    echo "    ✅ ISOLATION VERIFIED: Central Customer ({$c1->customer_code}) is NOT visible to Tenant A.\n";
} else {
    echo "    ❌ FAIL: Tenant A can see Central Customer ({$c1->customer_code})!\n";
}

echo "    - Creating Tenant A Customer (TA)...\n";
$ta = createCustomer('TenantA', '02');
echo "    ✅ Created Tenant A Customer: {$ta->first_name} (Code: {$ta->customer_code})\n";


// 3. Tenant B Context
echo "\n[3] Testing Tenant B Context:\n";
$tenantB = Tenant::firstOrCreate(['id' => 'tenant_b']);
echo "    - Initializing Tenant B...\n";
tenancy()->initialize($tenantB);
echo "    - Current Connection: " . DB::connection()->getName() . "\n";

// Check Visibility
$visibleC1_B = Customer::where('customer_code', $c1->customer_code)->first();
$visibleTA_B = Customer::where('customer_code', $ta->customer_code)->first();

if (!$visibleC1_B) {
    echo "    ✅ ISOLATION VERIFIED: Tenant B cannot see Central Customer ({$c1->customer_code}).\n";
} else {
    echo "    ❌ FAIL: Tenant B can see Central Customer ({$c1->customer_code})!\n";
}

if (!$visibleTA_B) {
    echo "    ✅ ISOLATION VERIFIED: Tenant B cannot see Tenant A's Customer ({$ta->customer_code}).\n";
} else {
    echo "    ❌ FAIL: Tenant B can see Tenant A's Customer ({$ta->customer_code})!\n";
}

echo "\n--------------------------------------------------\n";
echo "Verification Complete.\n";
