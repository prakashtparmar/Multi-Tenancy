<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Customer;
use App\Models\CustomerAddress;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Permissions
        $permissions = [
            // Dashboard
            'dashboard view',
            'analytics view',

            // User Management
            'users view',
            'users create',
            'users edit',
            'users delete',

            // Role Management
            'roles view',
            'roles create',
            'roles edit',
            'roles delete',

            // Catalog
            'products view',
            'products create',
            'products edit',
            'products delete',
            'inventory manage',

            // Sales
            'orders view',
            'orders manage',
            'customers view',
            'customers manage',

            // Marketing
            'marketing view',
            'marketing manage',

            // Reports
            'reports view',
            'reports export',

            // System
            'settings view',
            'settings manage',
            'activity-logs view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Define Roles & Assign Permissions

        // Super Admin (All Permissions)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Manager (Most Permissions, except destructive system actions)
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'dashboard view', 'analytics view',
            'users view', 'users create', 'users edit',
            'products view', 'products create', 'products edit', 'products delete', 'inventory manage',
            'orders view', 'orders manage',
            'customers view', 'customers manage',
            'marketing view', 'marketing manage',
            'reports view',
            'settings view',
        ]);

        // Editor (Content & Catalog focus)
        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editor->givePermissionTo([
            'dashboard view',
            'products view', 'products create', 'products edit',
            'inventory manage',
            'marketing view',
        ]);

        // Support (ReadOnly / Order focus)
        $support = Role::firstOrCreate(['name' => 'Support', 'guard_name' => 'web']);
        $support->givePermissionTo([
            'dashboard view',
            'orders view', 'orders manage',
            'customers view',
            'products view',
        ]);

        // 3. Create Default Admin User
        $tenantId = tenant('id');

        $user = User::firstOrCreate([
            'email' => "admin@{$tenantId}.com",
        ], [
            'name' => 'Tenant Admin',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($superAdmin);

        // 4. Create Demo Data (Customers)
        if (config('app.env') !== 'production') {
            $customer = Customer::create([
                'customer_code' => 'CUST-DEMO-001',
                'first_name' => 'Demo',
                'last_name' => 'Farmer',
                'mobile' => '9999999999',
                'email' => 'demo@example.com',
                'type' => 'farmer',
                'category' => 'individual',
                // 'village' => 'Model Village', // Moved to address
                // 'district' => 'Demo District', // Moved to address
                'land_area' => 10.5,
                'crops' => ['primary' => ['name' => 'Wheat', 'season' => 'Rabi']],
                'created_by' => $user->id,
            ]);

             CustomerAddress::create([
                'customer_id' => $customer->id,
                'type' => 'shipping',
                'address_line1' => 'Plot No 1, Farm Road',
                'village' => 'Model Village',
                'district' => 'Demo District',
                'state' => 'Maharashtra',
                'pincode' => '400001',
            ]);
        }
    }
}
