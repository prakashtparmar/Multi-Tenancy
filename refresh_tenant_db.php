<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Refreshing Tenant Database...\n";

// 1. Initialize Tenant
$tenant = Tenant::find('test_tenant');

if (!$tenant) {
    echo "Tenant not found, creating...\n";
    try {
        $tenant = Tenant::create(['id' => 'test_tenant']);
    } catch (\Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException $e) {
        echo "Database already exists, initializing anyway...\n";
        // Convert to model manually since create failed preventing return
        $tenant = new Tenant(['id' => 'test_tenant']);
        $tenant->saveQuietly(); // Save without triggering events
    }
}
tenancy()->initialize($tenant);
echo "Initialized tenant 'test_tenant'.\n";

// 2. Drop Tables
echo "Dropping tables from tenant connection...\n";
Schema::connection('tenant')->disableForeignKeyConstraints();
$tables = Schema::connection('tenant')->getTableListing();
foreach ($tables as $table) {
    Schema::connection('tenant')->drop($table);
    echo "Dropped $table\n";
}
Schema::connection('tenant')->enableForeignKeyConstraints();

// 3. Migrate
echo "Migrating...\n";
$kernel->call('tenants:migrate', ['--tenants' => [$tenant->id]]);

echo "Tenant database refreshed.\n";
