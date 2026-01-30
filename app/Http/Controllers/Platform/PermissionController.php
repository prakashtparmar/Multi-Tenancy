<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    private function getRoutePrefix()
    {
        return tenant() ? 'tenant' : 'central';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::with('roles')->latest()->paginate(15);

        return view('tenant.permissions.index', [
            'permissions' => $permissions,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenant.permissions.form', [
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'group' => ['nullable', 'string', 'max:50'],
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->route($this->getRoutePrefix().'.permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('tenant.permissions.form', [
            'permission' => $permission,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->route($this->getRoutePrefix().'.permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route($this->getRoutePrefix().'.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
