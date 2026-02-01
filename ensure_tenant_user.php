<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

try {
    $tenant = Tenant::find('client1');
    
    if (!$tenant) {
        echo "Creating tenant 'client1'...\n";
        $tenant = Tenant::create(['id' => 'client1']);
        $tenant->domains()->create(['domain' => 'client1.localhost']);
    } else {
        echo "Tenant 'client1' found.\n";
    }

    echo "Ensuring user exists in tenant...\n";
    $tenant->run(function () {
         $user = \App\Models\User::updateOrCreate(
             ['email' => 'admin@client1.com'],
             [
                 'name' => 'Tenant Admin',
                 'password' => bcrypt('password'),
             ]
         );
         echo "User 'admin@client1.com' ready (Password: password).\n";
         
         // Assign role if Spatie is used
         // $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
         // $user->assignRole($role);
    });

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
