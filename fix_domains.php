<?php

use App\Models\Tenant;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing Domains...\n";

$tenants = [
    'client1' => 'client1.localhost',
    'client2' => 'client2.localhost',
];

foreach ($tenants as $id => $domain) {
    $tenant = Tenant::find($id);
    if ($tenant) {
        if ($tenant->domains()->where('domain', $domain)->exists()) {
            echo "Domain $domain already exists for tenant $id.\n";
        } else {
            echo "Creating domain $domain for tenant $id...\n";
            $tenant->createDomain(['domain' => $domain]);
            echo "Created.\n";
        }
    } else {
        echo "Tenant $id not found.\n";
    }
}

echo "Done.\n";
