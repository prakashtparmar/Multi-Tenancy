<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CentralAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Permissions (Parity with Tenant)
        $permissions = [
            'dashboard view',
            'analytics view',
            'users view',
            'users create',
            'users edit',
            'users delete',
            'roles view',
            'roles create',
            'roles edit',
            'roles delete',
            'products view',
            'products create',
            'products edit',
            'products delete',
            'inventory manage',
            'purchase-orders view',
            'suppliers view',
            'warehouses view',
            'orders view',
            'orders manage',
            'customers view',
            'customers manage',
            'marketing view',
            'marketing manage',
            'reports view',
            'reports export',
            'settings view',
            'settings manage',
            'activity-logs view',
            'tenants view',
            'tenants manage', // Extra for Central
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Create Super Admin Role
        $superAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
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
    }
}
