<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        return view('central.tenants.index', [
            'tenants' => Tenant::with('domains')->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('central.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'min:3', 'max:20', 'alpha_dash', 'unique:tenants,id'],
            'domain_name' => ['required', 'string', 'min:3', 'max:20', 'alpha_dash', Rule::unique('domains', 'domain')->where(function ($query) {
                // Ensure subdomain uniqueness logic if needed
            })],
            'email' => ['required', 'email'],
        ]);

        try {
            // Assume localhost for dev, proper domain handling for prod would be needed
            $fullDomain = $validated['domain_name'].'.localhost';

            // Check if domain exists (though validation should catch this, double safety)
            if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $fullDomain)->exists()) {
                return back()->with('error', 'The subdomain "'.$validated['domain_name'].'" is already taken.');
            }

            $tenant = Tenant::create([
                'id' => $validated['id'],
                'status' => 'active', // Default to active
                'owner_email' => $validated['email'],
                'plan' => 'free',
            ]);
            $tenant->domains()->create(['domain' => $fullDomain]);

            // The TenantDatabaseSeeder is already run by the TenancyServiceProvider (Jobs\SeedDatabase)
            // due to the 'seeder_parameters' config in tenancy.php. 
            // We just need to update the admin email after it's done.
            $tenant->run(function () use ($validated) {
                $admin = \App\Models\User::first();
                if ($admin) {
                    $admin->update(['email' => $validated['email']]);
                }
            });

            return redirect(config('app.url').'/tenants')->with('success', 'Workspace provisioning complete.');

        } catch (\Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException $e) {
            return back()->with('error', 'Database collision detected for ID: '.$validated['id'].'. Please use a different Workspace ID.');
        } catch (\Exception $e) {
            \Log::error('Tenant creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'An unexpected error occurred: '.$e->getMessage());
        }
    }



    public function toggleStatus(Tenant $tenant)
    {
        $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';
        $tenant->update(['status' => $newStatus]);

        return back()->with('success', "Workspace {$tenant->id} is now {$newStatus}.");
    }
}
