<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

$tenants = Tenant::all();

if ($tenants->isEmpty()) {
    echo "No tenants found.\n";
} else {
    foreach ($tenants as $tenant) {
        echo "Tenant ID: " . $tenant->id . "\n";
        echo "Domains: " . $tenant->domains->pluck('domain')->implode(', ') . "\n";
    }
}
