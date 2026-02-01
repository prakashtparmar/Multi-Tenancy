<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

try {
    if (!Schema::hasTable('tenants')) {
        die("Error: 'tenants' table does not exist. Did you run migrations?\n");
    }

    if (Tenant::find('client1')) {
        echo "Tenant 'client1' already exists.\n";
    } else {
        echo "Creating tenant 'client1'...\n";
        $tenant = Tenant::create(['id' => 'client1']);
        $tenant->domains()->create(['domain' => 'client1.localhost']);
        
        // Also seed the tenant user so they can login!
        $tenant->run(function () {
             \App\Models\User::factory()->create([
                'name' => 'Tenant Admin',
                'email' => 'admin@client1.com',
                'password' => bcrypt('password'),
             ]);
             echo "User 'admin@client1.com' created inside tenant.\n";
        });

        echo "Tenant 'client1' created successfully with domain 'client1.localhost'.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
