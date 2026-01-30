<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = Tenant::firstOrCreate(['id' => 'test_tenant']);
tenancy()->initialize($tenant);

$columns = Schema::getColumnListing('customers');
echo "Columns in 'customers' table:\n";
print_r($columns);
