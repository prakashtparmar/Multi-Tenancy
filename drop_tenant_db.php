<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking for existing tenant database...\n";
    // Drop database if exists
    DB::statement('DROP DATABASE IF EXISTS tenant_foo');
    echo "Dropped tenant_foo database.\n";
} catch (\Exception $e) {
    echo 'Error dropping database: '.$e->getMessage()."\n";
}
