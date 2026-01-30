<?php

use App\Models\Tenant;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenantId = 'client1';

echo "Finding tenant: $tenantId\n";

$tenant = Tenant::find($tenantId);

if (! $tenant) {
    echo "Tenant '$tenantId' not found.\n";
    exit(1);
}

$tenant->run(function () {
    echo "Connected to tenant database.\n";

    $user = User::first();

    if (! $user) {
        echo "No users found in this tenant!\n";

        echo "Creating default admin user...\n";
        $user = User::create([
            'name' => 'Tenant Admin',
            'email' => 'admin@client1.com',
            'password' => bcrypt('password'),
        ]);
        echo "Created user: admin@client1.com / password\n";
    } else {
        echo 'Found user: '.$user->email."\n";
        $user->password = bcrypt('password');
        $user->save();
        echo "Password reset to: password\n";
    }
});
