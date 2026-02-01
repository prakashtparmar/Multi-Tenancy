<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\RouteContextService;

class PermissionController extends Controller
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
     * Display a listing of the permissions.
     */
    public function index(): View
    {
        $this->authorize('users manage');

        $permissions = Permission::with('roles')->latest()->paginate(15);

        return view('tenant.permissions.index', [
            'permissions' => $permissions,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): View
    {
        $this->authorize('users manage');

        return view('tenant.permissions.form', [
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('users manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'group' => ['nullable', 'string', 'max:50'],
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->route($this->getRoutePrefix() . '.permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission): View
    {
        $this->authorize('users manage');

        return view('tenant.permissions.form', [
            'permission' => $permission,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $this->authorize('users manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->route($this->getRoutePrefix() . '.permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorize('users manage');

        $permission->delete();

        return redirect()->route($this->getRoutePrefix() . '.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
