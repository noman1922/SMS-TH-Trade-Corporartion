<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        // STAFF MANAGEMENT
        $search = $request->input('search');

        $staff = User::query()
            ->where('role', 'staff')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff.index', compact('staff', 'search'));
    }

    public function create()
    {
        // STAFF MANAGEMENT
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {
        // STAFF MANAGEMENT
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'staff',
        ]);

        return redirect()->route('staff-management.index')
            ->with('success', 'Staff account created successfully.');
    }

    public function edit(User $staff_management)
    {
        // STAFF MANAGEMENT
        $this->ensureStaff($staff_management);

        return view('admin.staff.edit', ['staff' => $staff_management]);
    }

    public function update(Request $request, User $staff_management)
    {
        // STAFF MANAGEMENT
        $this->ensureStaff($staff_management);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff_management->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $staff_management->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'staff',
        ]);

        if (! empty($data['password'])) {
            $staff_management->password = $data['password'];
        }

        $staff_management->save();

        return redirect()->route('staff-management.index')
            ->with('success', 'Staff account updated successfully.');
    }

    public function destroy(User $staff_management)
    {
        // STAFF MANAGEMENT
        $this->ensureStaff($staff_management);

        $hasActivity = DB::table('invoices')->where('user_id', $staff_management->id)->exists()
            || DB::table('payments')->where('created_by', $staff_management->id)->exists()
            || DB::table('stock_histories')->where('created_by', $staff_management->id)->exists();

        if ($hasActivity) {
            return back()->with('error', 'This staff account has transaction history and cannot be deleted.');
        }

        DB::transaction(function () use ($staff_management) {
            $staff_management->delete();
        });

        return redirect()->route('staff-management.index')
            ->with('success', 'Staff account deleted successfully.');
    }

    private function ensureStaff(User $user): void
    {
        // STAFF MANAGEMENT
        // Only one admin account is allowed; staff management never mutates admin users.
        abort_unless($user->role === 'staff', 403);
    }
}
