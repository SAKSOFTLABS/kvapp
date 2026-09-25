<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $staffList = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('master.staff.index', compact('staffList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|in:Front Office,QC,Service',
            'mobile' => 'required|string|max:20',
            'address' => 'nullable|string',
            'username' => 'required|string|max:100|unique:staff,username|unique:users,username',
            'password' => 'required|string|min:6',
            'status' => 'required|in:active,inactive',
        ]);

        $roleMap = [
            'Front Office' => 'front_office',
            'QC' => 'qc',
            'Service' => 'service',
        ];
        $userRole = $roleMap[$validated['designation']] ?? 'staff';

        DB::transaction(function () use ($validated, $userRole) {
            $staff = Staff::create([
                'name' => $validated['name'],
                'designation' => $validated['designation'],
                'mobile' => $validated['mobile'],
                'address' => $validated['address'],
                'username' => $validated['username'],
                'status' => $validated['status'],
            ]);

            // Create corresponding User login account with designation-mapped role
            User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => strtolower($validated['username']) . '@keralavision.com',
                'password' => Hash::make($validated['password']),
                'role' => $userRole,
                'staff_id' => $staff->id,
                'status' => $validated['status'],
            ]);

            ActivityLogService::log('CREATE_STAFF', "Created staff member {$staff->name} ({$staff->username}) with designation {$staff->designation}");
        });

        return redirect()->route('staff.index')->with('success', "Staff member created successfully with {$validated['designation']} permissions.");
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|in:Front Office,QC,Service',
            'mobile' => 'required|string|max:20',
            'address' => 'nullable|string',
            'username' => 'required|string|max:100|unique:staff,username,' . $id . '|unique:users,username,' . ($staff->user ? $staff->user->id : 'NULL'),
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:active,inactive',
        ]);

        $roleMap = [
            'Front Office' => 'front_office',
            'QC' => 'qc',
            'Service' => 'service',
        ];
        $userRole = $roleMap[$validated['designation']] ?? 'staff';

        DB::transaction(function () use ($staff, $validated, $userRole) {
            $staff->update([
                'name' => $validated['name'],
                'designation' => $validated['designation'],
                'mobile' => $validated['mobile'],
                'address' => $validated['address'],
                'username' => $validated['username'],
                'status' => $validated['status'],
            ]);

            if ($staff->user) {
                $userData = [
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'role' => $userRole,
                    'status' => $validated['status'],
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $staff->user->update($userData);
            }

            ActivityLogService::log('UPDATE_STAFF', "Updated staff member {$staff->name} (Designation: {$staff->designation})");
        });

        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);

        // Check if staff has transfers or service records
        if ($staff->transfers()->count() > 0 || $staff->services()->count() > 0) {
            return back()->with('error', 'Cannot delete staff member with existing transfer or service records.');
        }

        DB::transaction(function () use ($staff) {
            if ($staff->user) {
                $staff->user->delete();
            }
            $staff->stocks()->delete();
            $staff->delete();

            ActivityLogService::log('DELETE_STAFF', "Deleted staff member {$staff->name}");
        });

        return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
    }

    public function jsonSearch(Request $request)
    {
        $term = $request->get('q', '');
        $staff = Staff::where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('designation', 'like', "%{$term}%")
                  ->orWhere('mobile', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'designation', 'mobile')
            ->get();

        return response()->json($staff);
    }
}
