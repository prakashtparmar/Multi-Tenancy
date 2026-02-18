<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CentralAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Permissions (Parity with Tenant)
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
            'permissions view', // Granular control
            'permissions manage',

            // Tenant Management (Central Only)
            'tenants view',
            'tenants create',
            'tenants edit',
            'tenants delete',
            'tenants manage', // For toggling status, etc.

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
            'brands view',
            'brands create',
            'brands edit',
            'brands delete',

            // Sales (Orders, Invoices, Shipments, Returns)
            'orders view',
            'orders create',
            'orders edit',
            'orders delete',
            'orders manage', // Legacy - keeping for now to avoid breaking other logic, but will phasing out usage in OrderController
            'orders verify', // For accessing verification page
            'orders approve',
            'orders process',
            'orders ship',
            'orders deliver',
            'orders cancel',
            'orders export',
            'orders import',
            'orders restore',
            'orders force-delete',
            'invoices view',
            'invoices create',
            'invoices edit',
            'invoices delete',
            'invoices manage', // Payments/Download
            'shipments view',
            'shipments create',
            'shipments edit',
            'shipments delete',
            'returns view',
            'returns create',
            'returns edit',
            'returns delete',
            'returns inspect',
            'returns manage',

            // CRM (Customers)
            'customers view',
            'customers create',
            'customers edit',
            'customers delete',
            'customers export',
            'customers manage', // Interactions/Credit limit

            // Operations (Inventory, Warehouses, Suppliers, POs)
            'inventory view',
            'inventory manage', // Adjustments
            'stock-transfers view',
            'stock-transfers create',
            'stock-transfers edit',
            'stock-transfers delete',
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
            'purchase-orders manage', // Receive stock

            // Marketing
            'marketing view',
            'marketing manage',

            // System & Logs
            'settings view',
            'settings manage', // Edit settings
            'activity-logs view',
            'reports view',
            'reports export',

            // Finance
            'finance view',
            'expenses manage',

            // Chat System
            'chat view',
            'chat manage',
            'chat create',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Create Super Admin Role
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 3. Create Master Admin User
        $user = User::firstOrCreate([
            'email' => 'master@admin.com',
        ], [
            'name' => 'Master Admin',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($superAdmin);


        // 4. Create CSR Role with specific permissions
        $csrPermissions = [
            'dashboard view',
            'analytics view',
            'orders view',
            'orders create',
            'orders edit',
            'customers view',
            'customers create',
            'customers edit',
            'customers manage',
            'returns view',
            'returns create',
            'returns edit',
            'returns delete',
        ];

        $csrRole = Role::firstOrCreate([
            'name' => 'CSR',
            'guard_name' => 'web'
        ]);

        $csrRole->syncPermissions(
            Permission::whereIn('name', $csrPermissions)->get()
        );


        // 5. Create Team Lead Role (example – adjust permissions as needed)
        $teamLeadPermissions = [
            'dashboard view',
            'analytics view',
            'orders view',
            'orders create',
            'orders edit',
            'orders approve',
            'customers view',
            'customers manage',
            'reports view',
        ];

        $teamLeadRole = Role::firstOrCreate([
            'name' => 'Team Lead',
            'guard_name' => 'web'
        ]);

        $teamLeadRole->syncPermissions(
            Permission::whereIn('name', $teamLeadPermissions)->get()
        );


        // 6. Create OV Role (example – usually read-only)
        $ovPermissions = [
            'dashboard view',
            'analytics view',
            'orders view',
            'orders create',
            'orders edit',
            'orders verify',
            'orders cancel',
            'customers view',
            'customers create',
            'customers edit',
            'customers manage',
        ];

        $ovRole = Role::firstOrCreate([
            'name' => 'OV',
            'guard_name' => 'web'
        ]);

        $ovRole->syncPermissions(
            Permission::whereIn('name', $ovPermissions)->get()
        );
    }
}
