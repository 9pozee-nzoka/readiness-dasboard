<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(Request $request): View
    {
        $departmentId = $request->integer('department') ?: null;
        $search       = $request->string('search')->trim()->value();

        $staff = User::with('department')
            ->where('is_approved', true)
            ->whereIn('role', [UserRole::Hod->value, UserRole::Employee->value])
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Performance: count requirements assigned to each user by responsible_officer name
        // We match on responsible_officer field (string) since requirements aren't FK-linked to users
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Build performance data per staff member
        $performance = $staff->getCollection()->map(function (User $user) {
            // Requirements in their department
            $deptReqs  = Requirement::where('department_id', $user->department_id)->get();
            $total     = $deptReqs->count();
            $completed = $deptReqs->where('is_completed', true)->count();
            $rate      = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            // Requirements specifically assigned to this user by name
            $assigned         = Requirement::where('responsible_officer', $user->name)->count();
            $assignedComplete = Requirement::where('responsible_officer', $user->name)->where('is_completed', true)->count();
            $personalRate     = $assigned > 0 ? (int) round(($assignedComplete / $assigned) * 100) : null;

            return [
                'user'         => $user,
                'dept_total'   => $total,
                'dept_done'    => $completed,
                'dept_rate'    => $rate,
                'assigned'     => $assigned,
                'assigned_done' => $assignedComplete,
                'personal_rate' => $personalRate,
            ];
        });

        return view('admin.staff.index', compact('staff', 'performance', 'departments', 'departmentId', 'search'));
    }
}
