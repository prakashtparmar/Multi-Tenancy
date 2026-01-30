<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Central Database Migration History:\n";
$migrations = DB::table('migrations')->orderBy('migration')->get();
foreach ($migrations as $m) {
    echo "- {$m->migration} (Batch: {$m->batch})\n";
}

echo "\nFiles in database/migrations:\n";
$files = glob(database_path('migrations/*.php'));
foreach ($files as $file) {
    echo "- " . basename($file) . "\n";
}
