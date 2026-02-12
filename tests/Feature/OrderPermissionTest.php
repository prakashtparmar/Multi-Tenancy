<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderPermissionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Create permissions if they don't exist
        if (!Permission::where('name', 'customers manage')->exists()) {
            Permission::create(['name' => 'customers manage']);
        }
    }

    public function test_api_store_customer_requires_permission()
    {
        $user = User::factory()->create();
        // User has NO permissions by default

        $response = $this->actingAs($user)
            ->postJson(route('central.api.customers.store-quick'), [
                'first_name' => 'Test Unauthorized',
                'mobile' => '9000000001'
            ]);

        $response->assertStatus(403);
    }

    public function test_api_store_customer_allows_permitted_user()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('customers manage');

        $response = $this->actingAs($user)
            ->postJson(route('central.api.customers.store-quick'), [
                'first_name' => 'Test Authorized',
                'mobile' => '9000000002'
            ]);

        // If validation fails (422), it means authorization passed (not 403).
        // If it succeeds (200/201), authorization passed.
        $this->assertNotEquals(403, $response->status());
    }

    public function test_api_store_address_requires_permission()
    {
        $user = User::factory()->create();
        // User has NO permissions by default

        $response = $this->actingAs($user)
            ->postJson(route('central.api.addresses.store'), [
                'address_line1' => 'Test Address',
                'city' => 'Test City'
            ]);

        $response->assertStatus(403);
    }


    public function test_api_store_interaction_allows_orders_manage_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('orders manage');
        // User DOES NOT have 'customers manage'

        $customer = \App\Models\Customer::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'mobile' => '1234567890',
            'email' => 'test@example.com',
            'type' => 'farmer', // Assuming type is required based on controller logic seen earlier? No, controller didn't enforce it in store but model might.
            // Let's check Customer model fillables if needed, but for now basic fields.
            // Wait, store method in controller required 'mobile' etc.
            // Let's just provide minimal fields.
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('central.customers.interaction', $customer), [
                'outcome' => 'Interested',
                'notes' => 'Test Interaction'
            ]);

        // If my hypothesis is correct, this will return 200 (or success), because we allowed 'orders manage'
        $response->assertStatus(200);
    }

}
