<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AdminController extends Controller
{
    /**
     * Get all users with their roles.
     */
    public function getUsers()
    {
        $users = User::with(['role', 'roles'])->paginate(500);
        
        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Get a specific user.
     */
    public function getUser($id)
    {
        $user = User::with(['role', 'roles'])->findOrFail($id);
        
        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Create a new user.
     */
    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
            'role_id' => ['required', 'exists:roles,id'],
            'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ]);

        // Assign role
        $role = Role::find($data['role_id']);
        $user->roles()->attach($role->id);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user->load(['role', 'roles'])
        ], 201);
    }

    /**
     * Update a user.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'phone_number' => ['sometimes', 'string', 'max:20', 'unique:users,phone_number,' . $id],
            'password' => ['sometimes', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
            'role_id' => ['sometimes', 'exists:roles,id'],
            'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Remove password from data if not provided
        if (!isset($data['password'])) {
            unset($data['password']);
        } else {
            // Hash the password if provided
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        // Update role if provided
        if (isset($data['role_id'])) {
            $user->roles()->detach();
            $role = Role::find($data['role_id']);
            $user->roles()->attach($role->id);
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->load(['role', 'roles'])
        ]);
    }

    /**
     * Delete a user.
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Cannot delete your own account.'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }

    /**
     * Get all roles.
     */
    public function getRoles()
    {
        $roles = Role::all();
        
        return response()->json([
            'roles' => $roles
        ]);
    }

    /**
     * Create a new role.
     */
    public function createRole(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $role = Role::create($data);

        return response()->json([
            'message' => 'Role created successfully.',
            'role' => $role
        ], 201);
    }

    /**
     * Update a role.
     */
    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,' . $id],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $role->update($data);

        return response()->json([
            'message' => 'Role updated successfully.',
            'role' => $role
        ]);
    }

    /**
     * Delete a role.
     */
    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deletion of system roles
        if (in_array($role->name, ['admin', 'manager', 'cashier', 'customer'])) {
            return response()->json([
                'message' => 'Cannot delete system roles.'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.'
        ]);
    }

    /**
     * Get system statistics.
     */
    public function getSystemStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_reservations' => \App\Models\Reservation::count(),
            'total_dishes' => \App\Models\Dish::count(),
            'total_tables' => \App\Models\Table::count(),
            // Count orders that are not yet finalized (delivered or failed)
            'pending_orders' => \App\Models\Order::whereIn('status', [
                'received', 'preparing', 'with_courier', 'out_for_delivery'
            ])->count(),
            'today_orders' => \App\Models\Order::today()->count(),
            'upcoming_reservations' => \App\Models\Reservation::upcoming()->count(),
        ];

        return response()->json([
            'stats' => $stats
        ]);
    }
}
