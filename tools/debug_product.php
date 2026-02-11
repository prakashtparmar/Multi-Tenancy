<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

$tenant = Tenant::first();
if ($tenant) {
    echo "Initializing Tenant {$tenant->id}\n";
    tenancy()->initialize($tenant);
} else {
    echo "No tenant found, running in central.\n";
}

try {
    $columns = DB::select('DESCRIBE products');
    foreach ($columns as $col) {
        // print_r($col);
        echo "Field: {$col->Field}, Type: {$col->Type}, Null: {$col->Null}, Key: {$col->Key}, Default: {$col->Default}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
