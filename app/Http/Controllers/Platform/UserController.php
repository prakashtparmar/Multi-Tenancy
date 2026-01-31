<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RouteContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search Filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        // Filter by Trashed
        if ($request->has('trashed') && $request->trashed === 'only') {
            $query->onlyTrashed();
        }

        $perPage = $request->input('per_page', 10);
        $users = $query->latest()->paginate($perPage)->withQueryString();

        return view('tenant.users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();

        return view('tenant.users.form', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['array'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'] ?? 'active',
        ]);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect(request()->root() . '/users')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();

        return view('tenant.users.form', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => ['array'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'] ?? $user->status,
        ]);

        if ($validated['password']) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect(request()->root() . '/users')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Check if user is already trashed to determine if force delete
        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        if ($user->trashed()) {
            $user->forceDelete();
            return redirect(request()->root() . '/users?trashed=only')->with('success', 'User permanently deleted.');
        } else {
            $user->delete();
            return redirect(request()->root() . '/users')->with('success', 'User moved to trash.');
        }
    }

    /**
     * Restore the specified resource.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect(request()->root() . '/users')->with('success', 'User restored successfully.');
    }

    /**
     * Handle Bulk Actions (Delete, Restore, Status)
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:users,id'],
            'action' => ['required', 'string', 'in:delete,restore,active,inactive,force_delete'],
        ]);

        $ids = $validated['ids'];
        $action = $validated['action'];

        // Exclude self from critical actions
        if (($key = array_search(auth()->id(), $ids)) !== false) {
            unset($ids[$key]);
            if (empty($ids)) return back()->with('error', 'You cannot perform actions on yourself.');
        }

        switch ($action) {
            case 'delete':
                User::whereIn('id', $ids)->delete();
                $message = 'Selected users moved to trash.';
                break;

            case 'force_delete':
                User::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                $message = 'Selected users permanently deleted.';
                break;

            case 'restore':
                User::onlyTrashed()->whereIn('id', $ids)->restore();
                $message = 'Selected users restored.';
                break;

            case 'active':
            case 'inactive':
                User::whereIn('id', $ids)->update(['status' => $action]);
                $message = "Selected users marked as $action.";
                break;
        }

        return back()->with('success', $message);
    }
}
