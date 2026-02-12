<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GranularOrderPermissionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure granular permissions exist
        $permissions = [
            'orders create',
            'orders edit',
            'orders approve',
            'orders process',
            'orders ship',
            'orders deliver',
            'orders cancel',
            'orders view'
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
    }

    public function test_order_status_update_requires_specific_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('orders view');

        $customer = Customer::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'mobile' => '1234567890',
            'email' => 'test@example.com',
            'type' => 'farmer'
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'code' => 'TWH',
            'is_active' => true
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $user->id
        ]);

        // User with ONLY view permission should fail to confirm
        $response = $this->actingAs($user)
            ->post(route('central.orders.update-status', $order), ['action' => 'confirm']);

        $response->assertStatus(403);

        // Give approve permission
        $user->givePermissionTo('orders approve');

        $response = $this->actingAs($user)
            ->post(route('central.orders.update-status', $order), ['action' => 'confirm']);

        // Should not be 403 anymore (likely 302 redirecting back)
        $this->assertNotEquals(403, $response->status());
    }

    public function test_order_creation_requires_orders_create_permission()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('central.orders.create'));
        $response->assertStatus(403);

        $user->givePermissionTo('orders create');
        $response = $this->actingAs($user)->get(route('central.orders.create'));
        $response->assertStatus(200);
    }
}
