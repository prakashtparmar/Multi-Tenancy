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
            'address_line1' => '123 Test St',
            'village' => 'Test Village',
            'state' => 'Test State',
            'pincode' => '12345',
            'country' => 'India',
            'is_default' => true,
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

        $response->assertRedirect();

        // Creator (admin) should receive notification
        Notification::assertSentTo($this->admin, OrderNotification::class, function ($notification) {
            return $notification->toArray($this->admin)['action'] === 'created';
        });
    }

    /** @test */
    public function it_sends_notification_to_creator_when_status_updated_by_admin()
    {
        Notification::fake();

        // Create a user who creates the order
        $creator = User::factory()->create();

        // Admin performs the action
        $admin = $this->admin;

        $order = Order::create([
            'order_number' => 'ORD-001',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $creator->id, // Created by User
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->actingAs($admin)->post(route('central.orders.update-status', $order), [
            'action' => 'confirm',
        ]);

        $response->assertRedirect();

        // Creator should receive notification
        Notification::assertSentTo($creator, OrderNotification::class, function ($notification) use ($creator) {
            return $notification->toArray($creator)['action'] === 'confirm';
        });

        // Admin (Actor) should NOT receive it (unless they are also Super Admin, but specifically targeting the "user notify" call)
        // Note: Admin might see it via God View, but the specific $order->creator->notify() only targets creator.
        // We can assert assertSentTo was NOT called for Admin IF Admin != Creator
        if ($admin->id !== $creator->id) {
            Notification::assertNotSentTo($admin, OrderNotification::class, function ($notification) use ($admin) {
                // We need to be careful here. If God View works by PULLING data, then assertNotSentTo is correct for PUSH notifications.
                // However, our code change was PUSH: $order->creator->notify().
                // So Admin should NOT get a PUSH notification.
                return $notification->toArray($admin)['action'] === 'confirm';
            });
        }
    }

    /** @test */
    public function it_sends_notification_to_creator_when_order_updated_by_admin()
    {
        Notification::fake();

        $creator = User::factory()->create();
        $admin = $this->admin;

        $order = Order::create([
            'order_number' => 'ORD-002',
            'status' => 'pending',
            'created_by' => $creator->id,
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->actingAs($admin)->put(route('central.orders.update', $order), [
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

        if (session('errors')) {
            dump(session('errors')->all());
        }
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        Notification::assertSentTo($creator, OrderNotification::class, function ($notification) use ($creator) {
            return $notification->toArray($creator)['action'] === 'updated';
        });
    }
}
