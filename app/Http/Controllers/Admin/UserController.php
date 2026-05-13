<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        // All actions in this controller require admin role
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    /**
     * List all users — pending approvals shown first.
     */
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'all');

        $users = User::with(['department', 'approvedBy'])
            ->when($filter->value() === 'pending', fn ($q) => $q->where('is_approved', false))
            ->when($filter->value() === 'approved', fn ($q) => $q->where('is_approved', true))
            ->orderByRaw('is_approved ASC')   // pending first
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $pendingCount = User::where('is_approved', false)->count();

        return view('admin.users.index', compact('users', 'pendingCount', 'filter'));
    }

    /**
     * Show the approve / edit role form for a single user.
     */
    public function edit(User $user): View
    {
        $departments  = Department::where('is_active', true)->orderBy('name')->get();
        $assignableRoles = [UserRole::Employee, UserRole::Hod, UserRole::Director, UserRole::Admin];

        return view('admin.users.edit', compact('user', 'departments', 'assignableRoles'));
    }

    /**
     * Save role + department assignment and approve the user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role'          => ['required', Rule::enum(UserRole::class)],
            'department_id' => [
                Rule::when(
                    fn () => in_array($request->input('role'), [UserRole::Hod->value, UserRole::Employee->value]),
                    ['required', 'exists:departments,id'],
                    ['nullable'],
                ),
            ],
        ], [
            'department_id.required' => 'A department is required for the selected role.',
        ]);

        $role = UserRole::from($validated['role']);

        // Clear department for non-scoped roles
        $departmentId = $role->isDepartmentScoped() ? $validated['department_id'] : null;

        $user->update([
            'role'          => $role,
            'department_id' => $departmentId,
        ]);

        // Approve if not already approved
        if (! $user->is_approved) {
            $user->approve(Auth::user(), $role);
        }

        return redirect()->route('admin.users.index')
            ->with('success', $user->name.' has been approved as '.$role->label().'.');
    }

    /**
     * Revoke access (set unapproved) without deleting the account.
     */
    public function revoke(User $user): RedirectResponse
    {
        abort_if($user->id === Auth::id(), 403, 'You cannot revoke your own access.');

        $user->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', $user->name.'\'s access has been revoked.');
    }

    /**
     * Permanently delete a user account.
     */
    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === Auth::id(), 403, 'You cannot delete your own account.');

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', $name.'\'s account has been deleted.');
    }
}
