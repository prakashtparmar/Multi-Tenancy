<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private function getRoutePrefix()
    {
        // Fix: Rely on the actual route name to determine context.
        $route = request()->route();
        if (!$route) {
            return 'central';
        }
        
        $routeName = $route->getName();
        return str_starts_with($routeName ?? '', 'tenant.') ? 'tenant' : 'central';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->paginate(10);
        
        // Fix: Force create URL to use current host
        $createUrl = route($this->getRoutePrefix() . '.roles.create');
        $currentHost = request()->getHttpHost();
        $createUrl = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $createUrl);

        return view('tenant.roles.index', [
            'roles' => $roles,
            'routePrefix' => $this->getRoutePrefix(),
            'createUrl' => $createUrl,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($data) {
            return explode(' ', $data->name)[0]; // Group by first word (e.g., "user" from "user create")
        });

        // Fix: Explicitly generate the action URL using the current request's host
        // to prevent route() from reverting to APP_URL (127.0.0.1) and causing 419 errors.
        $actionUrl = route($this->getRoutePrefix() . '.roles.store');
        
        // Ensure the host matches the current request to avoid cross-domain POSTs
        $currentHost = request()->getHttpHost(); 
        $actionUrl = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $actionUrl);

        // Fix: Generate safe Index URL for Back/Cancel buttons
        $indexUrl = route($this->getRoutePrefix() . '.roles.index');
        $indexUrl = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $indexUrl);

        return view('tenant.roles.form', [
            'permissions' => $permissions,
            'routePrefix' => $this->getRoutePrefix(),
            'actionUrl' => $actionUrl,
            'indexUrl' => $indexUrl,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        // Fix: Force redirect to current host to avoid session loss (419/Logout)
        $url = route($this->getRoutePrefix() . '.roles.index');
        $currentHost = request()->getHttpHost();
        $url = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $url);

        return redirect($url)->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($data) {
            return explode(' ', $data->name)[0];
        });

        // Fix logic for Edit as well
        $actionUrl = route($this->getRoutePrefix() . '.roles.update', $role);
        $currentHost = request()->getHttpHost();
        $actionUrl = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $actionUrl);

        // Fix: Generate safe Index URL for Back/Cancel buttons
        $indexUrl = route($this->getRoutePrefix() . '.roles.index');
        $indexUrl = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $indexUrl);

        return view('tenant.roles.form', [
            'role' => $role,
            'permissions' => $permissions,
            'routePrefix' => $this->getRoutePrefix(),
            'actionUrl' => $actionUrl,
            'indexUrl' => $indexUrl,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        // Fix: Force redirect to current host
        $url = route($this->getRoutePrefix() . '.roles.index');
        $currentHost = request()->getHttpHost();
        $url = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $url);

        return redirect($url)->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'admin' || $role->name === 'Super Admin') { // Prevent deleting specific critical roles
            return back()->with('error', 'Cannot delete system roles.');
        }

        $role->delete();
        
        // Fix: Force redirect to current host
        $url = route($this->getRoutePrefix() . '.roles.index');
        $currentHost = request()->getHttpHost();
        $url = preg_replace('#^https?://[^/]+#', (request()->secure() ? 'https://' : 'http://') . $currentHost, $url);

        return redirect($url)->with('success', 'Role deleted successfully.');
    }
}
