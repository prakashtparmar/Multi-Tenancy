<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $this->authorize('users manage');

        $query = User::with('roles');

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        // Filter by Trashed
        if ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        }

        $perPage = (int) $request->input('per_page', 10);
        $users = $query->latest()->paginate($perPage)->withQueryString();

        return view('tenant.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $this->authorize('users manage');

        $roles = Role::all();

        return view('tenant.users.form', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('users manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'] ?? 'active',
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('tenant.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $this->authorize('users manage');

        $roles = Role::all();

        return view('tenant.users.form', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('users manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'] ?? $user->status,
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('tenant.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $this->authorize('users manage');

        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        if ($user->trashed()) {
            $user->forceDelete();
            return redirect()->route('tenant.users.index', ['trashed' => 'only'])->with('success', 'User permanently deleted.');
        } else {
            $user->delete();
            return redirect()->route('tenant.users.index')->with('success', 'User moved to trash.');
        }
    }

    /**
     * Restore the specified user from trash.
     */
    public function restore($id): RedirectResponse
    {
        $this->authorize('users manage');

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('tenant.users.index')->with('success', 'User restored successfully.');
    }

    /**
     * Handle bulk actions on users.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $this->authorize('users manage');

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
            if (empty($ids)) {
                return back()->with('error', 'You cannot perform actions on yourself.');
            }
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
            default:
                $message = 'Invalid action.';
        }

        return back()->with('success', $message);
    }
}
