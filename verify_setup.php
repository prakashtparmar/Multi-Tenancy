<?php

/**
 * Complete Multi-Tenant Application Verification Script
 *
 * This script performs comprehensive checks on the multi-tenant setup
 */

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   Multi-Tenant Laravel Application - Verification Report   ║\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "\n";

$allPassed = true;

// Test 1: Central Database Connection
echo "📊 Test 1: Central Database Connection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
try {
    $centralDb = DB::connection()->getDatabaseName();
    echo "✓ Connected to central database: $centralDb\n";

    // Check tenants table
    if (Schema::hasTable('tenants')) {
        $tenantCount = Tenant::count();
        echo "✓ Tenants table exists with $tenantCount tenant(s)\n";
    } else {
        echo "✗ Tenants table not found!\n";
        $allPassed = false;
    }

    // Check domains table
    if (Schema::hasTable('domains')) {
        $domainCount = DB::table('domains')->count();
        echo "✓ Domains table exists with $domainCount domain(s)\n";
    } else {
        echo "✗ Domains table not found!\n";
        $allPassed = false;
    }

    // Check sessions table
    if (Schema::hasTable('sessions')) {
        echo "✓ Sessions table exists for database session storage\n";
    } else {
        echo "⚠️  Sessions table not found - session storage may not work\n";
    }

} catch (\Exception $e) {
    echo '✗ Central database connection failed: '.$e->getMessage()."\n";
    $allPassed = false;
}
echo "\n";

// Test 2: Tenant Enumeration
echo "📋 Test 2: Tenant Enumeration\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
try {
    $tenants = Tenant::with('domains')->get();

    if ($tenants->isEmpty()) {
        echo "⚠️  No tenants found. Create tenants to test multi-tenancy.\n";
    } else {
        echo "Found {$tenants->count()} tenant(s):\n\n";
        foreach ($tenants as $tenant) {
            echo "  Tenant ID: {$tenant->id}\n";
            $domains = $tenant->domains->pluck('domain')->implode(', ');
            echo "  Domains: $domains\n";
            echo "  Created: {$tenant->created_at->format('Y-m-d H:i:s')}\n";
            echo "\n";
        }
    }
} catch (\Exception $e) {
    echo '✗ Failed to enumerate tenants: '.$e->getMessage()."\n";
    $allPassed = false;
}
echo "\n";

// Test 3: Tenant Database Isolation
echo "🔒 Test 3: Tenant Database Isolation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$testedTenants = [];
foreach (Tenant::all() as $tenant) {
    try {
        tenancy()->initialize($tenant);
        $tenantDb = DB::connection()->getDatabaseName();
        $expectedDb = "tenant_{$tenant->id}";

        if ($tenantDb === $expectedDb) {
            echo "✓ Tenant '{$tenant->id}' connected to correct database: $tenantDb\n";

            // Check users table
            if (Schema::hasTable('users')) {
                $userCount = User::count();
                echo "  └─ Users table exists with $userCount user(s)\n";

                // List users
                $users = User::all();
                foreach ($users as $user) {
                    echo "     • {$user->email} ({$user->name})\n";
                }
            } else {
                echo "  └─ ✗ Users table not found - run migrations!\n";
                $allPassed = false;
            }
        } else {
            echo "✗ Tenant '{$tenant->id}' database mismatch!\n";
            echo "  Expected: $expectedDb, Got: $tenantDb\n";
            $allPassed = false;
        }

        $testedTenants[] = $tenant->id;
        tenancy()->end();

    } catch (\Exception $e) {
        echo "✗ Failed to test tenant '{$tenant->id}': ".$e->getMessage()."\n";
        $allPassed = false;
    }
    echo "\n";
}

if (empty($testedTenants)) {
    echo "⚠️  No tenants available to test database isolation\n\n";
}

// Test 4: Configuration Checks
echo "⚙️  Test 4: Configuration Checks\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$sessionDomain = config('session.domain');
if ($sessionDomain === '.localhost') {
    echo "✓ Session domain configured for subdomain sharing: $sessionDomain\n";
} elseif ($sessionDomain === null) {
    echo "⚠️  Session domain is null - sessions won't work across subdomains\n";
    echo "   Set SESSION_DOMAIN=.localhost in .env file\n";
} else {
    echo "✓ Session domain: $sessionDomain\n";
}

$sessionDriver = config('session.driver');
echo "✓ Session driver: $sessionDriver\n";

$centralDomains = config('tenancy.central_domains');
echo '✓ Central domains: '.implode(', ', $centralDomains)."\n";

$dbPrefix = config('tenancy.database.prefix');
$dbSuffix = config('tenancy.database.suffix');
echo "✓ Tenant database naming: {$dbPrefix}[tenant_id]{$dbSuffix}\n";

$seederClass = config('tenancy.seeder_parameters.--class');
echo "✓ Default tenant seeder: $seederClass\n";

echo "\n";

// Test 5: Security Middleware
echo "🛡️  Test 5: Security Middleware\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$middlewareClasses = [
    'EnsureTenantSession' => 'App\\Http\\Middleware\\EnsureTenantSession',
    'ValidateTenantAccess' => 'App\\Http\\Middleware\\ValidateTenantAccess',
];

foreach ($middlewareClasses as $name => $class) {
    if (class_exists($class)) {
        echo "✓ Security middleware exists: $name\n";
    } else {
        echo "✗ Security middleware missing: $name\n";
        $allPassed = false;
    }
}

echo "\n";

// Final Summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                     VERIFICATION SUMMARY                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($allPassed) {
    echo "✅ ALL TESTS PASSED!\n";
    echo "\n";
    echo "Your multi-tenant application is properly configured.\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "1. Start the development server: php artisan serve\n";
    echo "2. Access central domain: http://127.0.0.1:8000\n";
    if (! empty($testedTenants)) {
        $firstTenant = $testedTenants[0];
        echo "3. Access tenant domain: http://{$firstTenant}.localhost:8000\n";
        echo "4. Login with: admin@{$firstTenant}.com / password\n";
    }
    echo "\n";
} else {
    echo "⚠️  SOME TESTS FAILED\n";
    echo "\n";
    echo "Please review the errors above and fix the issues.\n";
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
