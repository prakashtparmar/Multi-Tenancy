<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\RouteContextService;

class RoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get the route prefix based on context.
     */
    private function getRoutePrefix(): string
    {
        return RouteContextService::getRoutePrefix();
    }

    /**
     * Display a listing of the roles.
     */
    public function index(): View
    {
        $this->authorize('users manage');

        $roles = Role::with('permissions')->withCount('users')->paginate(10);
        
        return view('tenant.roles.index', [
            'roles' => $roles,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $this->authorize('users manage');

        $permissions = Permission::all()->groupBy(fn($p) => explode(' ', $p->name)[0]);

        return view('tenant.roles.form', [
            'permissions' => $permissions,
            'routePrefix' => $this->getRoutePrefix(),
            'role' => new Role(), // For form model binding if used
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('users manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route($this->getRoutePrefix() . '.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $this->authorize('users manage');

        $permissions = Permission::all()->groupBy(fn($p) => explode(' ', $p->name)[0]);

        return view('tenant.roles.form', [
            'role' => $role,
            'permissions' => $permissions,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('users manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route($this->getRoutePrefix() . '.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('users manage');

        if (in_array($role->name, ['admin', 'Super Admin'])) {
            return back()->with('error', 'Cannot delete system roles.');
        }

        $role->delete();
        
        return redirect()->route($this->getRoutePrefix() . '.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
