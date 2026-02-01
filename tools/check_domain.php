<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$domain = 'client1.localhost';

echo "Checking status for domain: $domain\n";
echo "----------------------------------------\n";

$domainRecord = DB::table('domains')->where('domain', $domain)->first();

if ($domainRecord) {
    echo "Result: Domain FOUND in 'domains' table.\n";
    echo "Tenant ID: " . $domainRecord->tenant_id . "\n";
    
    $tenantRecord = DB::table('tenants')->where('id', $domainRecord->tenant_id)->first();
    
    if ($tenantRecord) {
        echo "Result: Tenant FOUND in 'tenants' table.\n";
        echo "Status: " . ($tenantRecord->status ?? 'N/A (Column missing)') . "\n";
        echo "Created At: " . $tenantRecord->created_at . "\n";
        
        $data = json_decode($tenantRecord->data ?? '{}', true);
        if ($data) {
            echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
        
    } else {
         echo "Result: Tenant NOT FOUND in 'tenants' table. (ORPHANED DOMAIN)\n";
         echo "The domain points to a tenant ID that does not exist.\n";
    }
    
} else {
    echo "Result: Domain NOT FOUND in 'domains' table.\n";
    echo "Note: If the domain is not in the database, the application should throw 'TenantNotIdentified' exception.\n";
}

echo "----------------------------------------\n";
