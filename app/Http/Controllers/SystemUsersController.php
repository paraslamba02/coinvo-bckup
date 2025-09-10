<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SystemUsersController extends Controller
{
    /**
     * Display a listing of system users
     */
    public function index(Request $request)
    {
        // Only superusers can access this
        if (auth()->user()->role->name !== 'superuser') {
            abort(403, 'Unauthorized access');
        }

        $query = User::with('role')
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['admin', 'superuser']);
            });

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::whereIn('name', ['admin', 'superuser'])->get();

        return Inertia::render('SystemUsers', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role'])
        ]);
    }

    /**
     * Store a newly created system user
     */
    public function store(Request $request)
    {
        if (auth()->user()->role->name !== 'superuser') {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => ['required', Rule::exists('roles', 'id')->whereIn('name', ['admin', 'superuser'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->back()->with('success', 'System user created successfully!');
    }

    /**
     * Update the specified system user
     */
    public function update(Request $request, User $user)
    {
        if (auth()->user()->role->name !== 'superuser') {
            abort(403, 'Unauthorized access');
        }

        // Prevent superuser from demoting themselves
        if ($user->id === auth()->id() && $request->get('role_id') != $user->role_id) {
            return redirect()->back()->withErrors(['role_id' => 'You cannot change your own role.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => ['required', Rule::exists('roles', 'id')->whereIn('name', ['admin', 'superuser'])],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back()->with('success', 'System user updated successfully!');
    }

    /**
     * Remove the specified system user
     */
    public function destroy(User $user)
    {
        if (auth()->user()->role->name !== 'superuser') {
            abort(403, 'Unauthorized access');
        }

        // Prevent superuser from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Only allow deletion of admin and superuser accounts
        if (!in_array($user->role->name, ['admin', 'superuser'])) {
            return redirect()->back()->withErrors(['error' => 'You can only delete system users.']);
        }

        $user->delete();

        return redirect()->back()->with('success', 'System user deleted successfully!');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if (auth()->user()->role->name !== 'superuser') {
            abort(403, 'Unauthorized access');
        }

        // Prevent superuser from deactivating themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        return redirect()->back()->with('success', 'User status updated successfully!');
    }
}