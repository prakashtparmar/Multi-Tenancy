<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Create permissions if they don't exist
        Permission::firstOrCreate(['name' => 'orders view', 'guard_name' => 'web']);

        // Create Super Admin Role
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    /** @test */
    public function regular_user_sees_only_own_notifications()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create notification for user 1
        $id1 = Str::uuid()->toString();
        DB::table('notifications')->insert([
            'id' => $id1,
            'type' => 'App\Notifications\OrderNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user1->id,
            'data' => json_encode(['message' => 'Order 1']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create notification for user 2
        $id2 = Str::uuid()->toString();
        DB::table('notifications')->insert([
            'id' => $id2,
            'type' => 'App\Notifications\OrderNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user2->id,
            'data' => json_encode(['message' => 'Order 2']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user1)->getJson(route('central.notifications.index'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $id1]);
        $response->assertJsonMissing(['id' => $id2]);
    }

    /** @test */
    public function super_admin_sees_all_order_notifications()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create notification for user 1
        $id1 = Str::uuid()->toString();
        DB::table('notifications')->insert([
            'id' => $id1,
            'type' => 'App\Notifications\OrderNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user1->id,
            'data' => json_encode(['message' => 'Order 1']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create notification for user 2
        $id2 = Str::uuid()->toString();
        DB::table('notifications')->insert([
            'id' => $id2,
            'type' => 'App\Notifications\OrderNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user2->id,
            'data' => json_encode(['message' => 'Order 2']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a non-order notification
        $id3 = Str::uuid()->toString();
        DB::table('notifications')->insert([
            'id' => $id3,
            'type' => 'App\Notifications\OtherNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user1->id,
            'data' => json_encode(['message' => 'Other']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('central.notifications.index'));

        $response->assertOk();

        // Should find id1 and id2. 
        // Note: The controller limits to 10 latest.
        $response->assertJsonFragment(['id' => $id1]);
        $response->assertJsonFragment(['id' => $id2]);

        // Should NOT find id3 because it is not OrderNotification (and strict check is implemented)
        $response->assertJsonMissing(['id' => $id3]);
    }
}
