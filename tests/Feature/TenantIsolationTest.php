<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantIsolationTest extends TestCase
{
    // Use simple transaction rollbacks if possible, but tenant DBs make this tricky.
    // We'll create temporary tenants.

    public function test_central_login_page_works()
    {
        $response = $this->get('http://localhost:8000/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_authenticated_user_on_central_has_access()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('http://localhost:8000/dashboard');
            
        $response->assertStatus(200);
    }
    
    public function test_tenant_login_page_works()
    {
        // Need a tenant
        $tenantId = 'test_iso_' . uniqid();
        $tenant = Tenant::create(['id' => $tenantId]);
        $tenant->domains()->create(['domain' => $tenantId . '.localhost']);

        $response = $this->get("http://{$tenantId}.localhost:8000/login");
        
        $response->assertStatus(200);
        // Should verify it is the tenant login view (they share the view file usually)
    }

    public function test_central_session_rejected_on_tenant()
    {
        // 1. Create User and Tenant
        $user = User::factory()->create();
        $tenantId = 'test_iso_' . uniqid();
        $tenant = Tenant::create(['id' => $tenantId]);
        $tenant->domains()->create(['domain' => $tenantId . '.localhost']);
        
        // 2. Simulate User logged in on Central (session has no tenant_id)
        // We simulate the leaking of the session by NOT putting tenant_id
        
        $response = $this->actingAs($user)
            ->get("http://{$tenantId}.localhost:8000/dashboard");
            
        // 3. Should be redirected to login with error, and logged out
        $response->assertStatus(302);
        $this->followRedirects($response)->assertSee('Session invalid');
    }
    
    public function test_tenant_session_rejected_on_central()
    {
        // 1. Create User
        $user = User::factory()->create();

        // 2. Simulate User logged in with a tenant_id in session
        $response = $this->actingAs($user)
            ->withSession(['tenant_id' => 'some_tenant'])
            ->get('http://localhost:8000/dashboard');

        // 3. EnsureCentralSession should catch this
        $response->assertStatus(302);
        // Should redirect to central login
        
        // $this->followRedirects($response)->assertSee('Session mismatch');
    }
}
