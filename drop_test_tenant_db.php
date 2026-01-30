<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Dropping Tenant Database...\n";
try {
    // Drop the database if it exists
    DB::statement("DROP DATABASE IF EXISTS tenant_test_tenant");
    echo "Dropped database 'tenant_test_tenant'.\n";
} catch (\Exception $e) {
    echo "Error dropping database: " . $e->getMessage() . "\n";
}
