<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dbName = DB::connection()->getDatabaseName();
$tables = DB::select('SHOW TABLES');
$tableKey = "Tables_in_{$dbName}";

echo "Database: $dbName\n";
echo "Tables:\n";
foreach ($tables as $table) {
    echo "- " . $table->$tableKey . "\n";
}

echo "\nMigration Paths:\n";
$migrator = app('migrator');
foreach ($migrator->paths() as $path) {
    echo "- $path\n";
}
