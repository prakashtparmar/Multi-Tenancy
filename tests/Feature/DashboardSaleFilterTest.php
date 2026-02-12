<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DashboardSaleFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_sales_excludes_cancelled_and_scheduled_orders()
    {
        // Ensure permission exists
        Permission::firstOrCreate(['name' => 'analytics view', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('analytics view');
        $this->actingAs($user);

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

        // 1. Pending Order (Included)
        Order::create([
            'order_number' => 'ORD-PENDING',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'total_amount' => 1000,
            'grand_total' => 1000,
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $user->id
        ]);

        // 2. Scheduled Order (Excluded)
        Order::create([
            'order_number' => 'ORD-SCHEDULED',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'total_amount' => 5000,
            'grand_total' => 5000,
            'status' => 'scheduled',
            'placed_at' => now(),
            'created_by' => $user->id
        ]);

        // 3. Cancelled Order (Excluded)
        Order::create([
            'order_number' => 'ORD-CANCELLED',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'total_amount' => 2000,
            'grand_total' => 2000,
            'status' => 'cancelled',
            'placed_at' => now(),
            'created_by' => $user->id
        ]);

        $response = $this->get(route('dashboard', ['period' => 'today']));

        // Value should be 1000 (only the pending order)
        $response->assertSee('Rs 1,000.00');
        $response->assertDontSee('Rs 8,000.00'); // Sum of all
        $response->assertDontSee('Rs 6,000.00'); // Sum of pending + scheduled or cancelled

        // But the orders should still be visible in the history/list
        $response->assertSee('ORD-PENDING');
        $response->assertSee('ORD-SCHEDULED');
        $response->assertSee('ORD-CANCELLED');
    }
}
