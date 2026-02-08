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
            // Dashboard & Analytics
            'dashboard view',
            'analytics view',

            // User Management
            'users view',
            'users create',
            'users edit',
            'users delete',
            'users export',

            // Role & Permission Management
            'roles view',
            'roles create',
            'roles edit',
            'roles delete',
            'permissions view',

            // Catalog (Products, Categories, Collections)
            'products view',
            'products create',
            'products edit',
            'products delete',
            'products export',
            'products import',
            'categories view',
            'categories create',
            'categories edit',
            'categories delete',
            'collections view',
            'collections create',
            'collections edit',
            'collections delete',

            // Sales (Orders, Invoices, Shipments, Returns)
            'orders view',
            'orders create',
            'orders edit',
            'orders delete',
            'orders manage',
            'orders export',
            'invoices view',
            'invoices create',
            'invoices edit',
            'invoices delete',
            'invoices manage',
            'shipments view',
            'shipments create',
            'shipments edit',
            'shipments delete',
            'returns view',
            'returns create',
            'returns edit',
            'returns delete',

            // CRM (Customers)
            'customers view',
            'customers create',
            'customers edit',
            'customers delete',
            'customers export',
            'customers manage',

            // Operations (Inventory, Warehouses, Suppliers, POs)
            'inventory view',
            'inventory manage',
            'warehouses view',
            'warehouses create',
            'warehouses edit',
            'warehouses delete',
            'suppliers view',
            'suppliers create',
            'suppliers edit',
            'suppliers delete',
            'purchase-orders view',
            'purchase-orders create',
            'purchase-orders edit',
            'purchase-orders delete',
            'purchase-orders manage',

            // Marketing
            'marketing view',
            'marketing manage',

            // System & Logs
            'settings view',
            'settings manage',
            'activity-logs view',
            'reports view',
            'reports export',
            'reports view',
            'reports export',

            // Finance
            'finance view',
            'expenses manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Define Roles & Assign Permissions

        // Super Admin (All Permissions)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Manager (Most Permissions, except destructive user/system actions)
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'dashboard view',
            'analytics view',
            'users view',
            'users create',
            'users edit',
            'products view',
            'products create',
            'products edit',
            'products delete',
            'products export',
            'categories view',
            'categories create',
            'categories edit',
            'inventory view',
            'inventory manage',
            'orders view',
            'orders create',
            'orders edit',
            'orders manage',
            'customers view',
            'customers create',
            'customers edit',
            'customers manage',
            'purchase-orders view',
            'purchase-orders create',
            'purchase-orders edit',
            'purchase-orders manage',
            'suppliers view',
            'suppliers create',
            'warehouses view',
            'reports view',
            'reports export',
            'reports view',
            'reports export',
            'finance view',
            'expenses manage',
        ]);

        // Editor (Content & Catalog focus)
        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editor->givePermissionTo([
            'dashboard view',
            'products view',
            'products create',
            'products edit',
            'products export',
            'products import',
            'categories view',
            'categories create',
            'categories edit',
            'collections view',
            'collections create',
            'collections edit',
            'inventory manage',
            'marketing view',
        ]);

        // Support (ReadOnly / Order focus)
        $support = Role::firstOrCreate(['name' => 'Support', 'guard_name' => 'web']);
        $support->givePermissionTo([
            'dashboard view',
            'orders view',
            'orders manage',
            'orders export',
            'customers view',
            'products view',
            'shipments view',
            'returns view',
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
            $customer = Customer::firstOrCreate([
                'customer_code' => 'CUST-DEMO-001',
            ], [
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
