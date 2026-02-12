<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Product;
use App\Models\CustomerAddress;
use App\Notifications\OrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;
    protected $warehouse;
    protected $product;
    protected $address;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        foreach (['orders create', 'orders edit', 'orders approve', 'orders manage', 'orders view', 'orders cancel', 'orders ship', 'orders deliver', 'orders process'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(['orders create', 'orders edit', 'orders approve', 'orders manage', 'orders view']);

        $this->customer = Customer::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'mobile' => '1234567890',
            'email' => 'test@example.com',
            'type' => 'farmer'
        ]);

        $this->address = CustomerAddress::create([
            'customer_id' => $this->customer->id,
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'country' => 'India',
            'is_default_billing' => true,
            'is_default_shipping' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'code' => 'TWH',
            'is_active' => true
        ]);

        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU',
            'price' => 100,
            'is_active' => true,
            'stock_on_hand' => 100,
        ]);
    }

    /** @test */
    public function it_sends_notification_when_order_is_created()
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)->post(route('central.orders.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => 100,
                ]
            ],
            'billing_address_id' => $this->address->id,
            'payment_method' => 'cash',
            'shipping_method' => 'standard',
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo($this->admin, OrderNotification::class, function ($notification) {
            return $notification->toArray($this->admin)['action'] === 'created';
        });
    }

    /** @test */
    public function it_sends_notification_when_order_status_is_updated()
    {
        Notification::fake();

        $order = Order::create([
            'order_number' => 'ORD-001',
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

        $response = $this->actingAs($this->admin)->post(route('central.orders.update-status', $order), [
            'action' => 'confirm',
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($this->admin, OrderNotification::class, function ($notification) {
            return $notification->toArray($this->admin)['action'] === 'confirm';
        });
    }

    /** @test */
    public function it_sends_notification_when_order_is_updated()
    {
        Notification::fake();

        $order = Order::create([
            'order_number' => 'ORD-002',
            'status' => 'pending',
            'created_by' => $this->admin->id,
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('central.orders.update', $order), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                    'price' => 150,
                ]
            ],
            'billing_address_id' => $this->address->id,
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo($this->admin, OrderNotification::class, function ($notification) {
            return $notification->toArray($this->admin)['action'] === 'updated';
        });
    }
}
