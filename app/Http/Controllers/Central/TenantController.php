<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException;

class TenantController extends Controller
{
    /**
     * Display a listing of the tenants.
     */
    public function index(): View
    {
        return view('central.tenants.index', [
            'tenants' => Tenant::with('domains')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create(): View
    {
        return view('central.tenants.create');
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'min:3', 'max:20', 'alpha_dash', 'unique:tenants,id'],
            'domain_name' => ['required', 'string', 'min:3', 'max:20', 'alpha_dash', Rule::unique('domains', 'domain')],
            'email' => ['required', 'email'],
        ]);

        try {
            // Assume localhost for dev, proper domain handling for prod would be needed via configuration
            $baseDomain = request()->getHost();
            if ($baseDomain === '127.0.0.1') {
                $baseDomain = 'localhost';
            }
            // If it's a subdomain, we should get the root domain. 
            // For now, let's stick to the simplest production-ready logic.
            $fullDomain = $validated['domain_name'] . '.' . $baseDomain;

            if (Domain::where('domain', $fullDomain)->exists()) {
                return back()->withInput()->with('error', 'The subdomain "' . $validated['domain_name'] . '" is already taken.');
            }

            $tenant = Tenant::create([
                'id' => $validated['id'],
                'status' => 'active',
                'owner_email' => $validated['email'],
                'plan' => 'free',
            ]);
            
            $tenant->domains()->create(['domain' => $fullDomain]);

            // Seed tenant specific data and update admin email
            $tenant->run(function () use ($validated) {
                $admin = User::first();
                if ($admin) {
                    $admin->update(['email' => $validated['email']]);
                }
            });

            return redirect()->route('tenants.index')->with('success', 'Workspace provisioning complete.');

        } catch (TenantDatabaseAlreadyExistsException $e) {
            return back()->withInput()->with('error', 'Database collision detected for ID: ' . $validated['id']);
        } catch (\Exception $e) {
            Log::error('Tenant creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of a tenant.
     */
    public function toggleStatus(Tenant $tenant): RedirectResponse
    {
        $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';
        $tenant->update(['status' => $newStatus]);

        return back()->with('success', "Workspace {$tenant->id} is now {$newStatus}.");
    }
}
