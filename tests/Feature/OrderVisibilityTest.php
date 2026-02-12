<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $customer;
    protected $address;
    protected $warehouse;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles and permissions
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $permission = Permission::firstOrCreate(['name' => 'orders view']);
        $role->givePermissionTo($permission);

        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userRole->givePermissionTo($permission);

        // Create Super Admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        // Create Regular User
        $this->user = User::factory()->create();
        $this->user->assignRole('User');

        // Setup common data
        $this->customer = Customer::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test@customer.com',
            'phone' => '1234567890',
        ]);

        $this->address = CustomerAddress::create([
            'customer_id' => $this->customer->id,
            'address_line1' => '123 Test St',
            'village' => 'Test Village',
            'state' => 'Test State',
            'pincode' => '12345',
            'country' => 'India',
            'is_default' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'code' => 'WH-001',
            'address' => 'Test Address',
            'contact_number' => '1234567890',
            'email' => 'warehouse@test.com',
        ]);

        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP-001',
            'price' => 100,
            'stock_quantity' => 10,
        ]);
    }

    /** @test */
    public function super_admin_can_view_all_orders()
    {
        // Order created by User
        $order1 = Order::create([
            'order_number' => 'ORD-001',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $this->user->id,
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
        ]);

        // Order created by Admin
        $order2 = Order::create([
            'order_number' => 'ORD-002',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $this->admin->id,
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('central.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('ORD-001'); // User's order
        $response->assertSee('ORD-002'); // Admin's order
    }

    /** @test */
    public function regular_user_can_view_only_own_orders()
    {
        // Order created by User
        $order1 = Order::create([
            'order_number' => 'ORD-003',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $this->user->id,
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
        ]);

        // Order created by Admin (Others)
        $order2 = Order::create([
            'order_number' => 'ORD-004',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $this->admin->id,
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('central.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('ORD-003'); // Can see own
        $response->assertDontSee('ORD-004'); // Cannot see others
    }
}
