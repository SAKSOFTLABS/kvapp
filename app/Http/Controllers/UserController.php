<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Staff;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('staff');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();
        $staffList = Staff::where('status', 'active')->orderBy('name')->get();

        return view('master.users', compact('users', 'staffList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,front_office,qc,service,staff',
            'staff_id' => 'nullable|exists:staff,id',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLogService::log('CREATE_USER', "Created new user account '{$user->username}' with role '{$user->role}'");

        return redirect()->route('users.index')->with('success', "User '{$user->username}' created successfully.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,front_office,qc,service,staff',
            'staff_id' => 'nullable|exists:staff,id',
            'status' => 'required|in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLogService::log('UPDATE_USER', "Updated user account '{$user->username}'");

        return redirect()->route('users.index')->with('success', "User '{$user->username}' updated successfully.");
    }

    public function destroy($id)
    {
        if ($id == Auth::id()) {
            return back()->with('error', 'You cannot delete your own logged-in account.');
        }

        $user = User::findOrFail($id);
        $username = $user->username;
        $user->delete();

        ActivityLogService::log('DELETE_USER', "Deleted user account '{$username}'");

        return redirect()->route('users.index')->with('success', "User '{$username}' deleted successfully.");
    }
}
