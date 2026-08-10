<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $assignableRoles = [UserRole::Hod, UserRole::Employee];

        return view('admin.staff.create', compact('departments', 'assignableRoles'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in([UserRole::Hod->value, UserRole::Employee->value])],
            'department_id' => ['required', 'exists:departments,id'],
        ], [
            'role.in' => 'Staff role must be Head of Department or Employee.',
        ]);

        $role = UserRole::from($validated['role']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'department_id' => $validated['department_id'],
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', $user->name.' has been created as '.$role->label().'.');
    }

    public function index(Request $request): View
    {
        $departmentId = $request->integer('department') ?: null;
        $search = $request->string('search')->trim()->value();

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

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $performance = $staff->getCollection()->map(function (User $user) {
            // Department-wide requirements
            $deptReqs = Requirement::where('department_id', $user->department_id)->get();
            $total = $deptReqs->count();
            $completed = $deptReqs->where('is_completed', true)->count();
            $rate = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            // Personally assigned requirements
            $assigned = Requirement::where('responsible_officer', $user->name)->count();
            $assignedComplete = Requirement::where('responsible_officer', $user->name)->where('is_completed', true)->count();
            $personalRate = $assigned > 0 ? (int) round(($assignedComplete / $assigned) * 100) : null;

            // Average completion time (days) for assigned completed tasks
            $avgCompletionDays = null;
            $completedWithDeadline = Requirement::where('responsible_officer', $user->name)
                ->where('is_completed', true)
                ->whereNotNull('deadline')
                ->whereNotNull('completed_at')
                ->get();

            if ($completedWithDeadline->isNotEmpty()) {
                $totalDays = $completedWithDeadline->sum(function ($r) {
                    return max(0, $r->deadline->diffInDays($r->completed_at, false));
                });
                $avgCompletionDays = round($totalDays / $completedWithDeadline->count(), 1);
            }

            // SLA compliance — completed before deadline
            $slaCompliant = 0;
            $slaMissed = 0;
            if ($completedWithDeadline->isNotEmpty()) {
                $slaCompliant = $completedWithDeadline->filter(fn ($r) => $r->completed_at->lte($r->deadline))->count();
                $slaMissed = $completedWithDeadline->count() - $slaCompliant;
            }
            $slaRate = $completedWithDeadline->count() > 0
                ? (int) round(($slaCompliant / $completedWithDeadline->count()) * 100)
                : null;

            // Overdue tasks
            $overdueCount = Requirement::where('responsible_officer', $user->name)
                ->where('is_completed', false)
                ->whereNotNull('deadline')
                ->where('deadline', '<', now()->toDateString())
                ->count();

            return [
                'user' => $user,
                'dept_total' => $total,
                'dept_done' => $completed,
                'dept_rate' => $rate,
                'assigned' => $assigned,
                'assigned_done' => $assignedComplete,
                'personal_rate' => $personalRate,
                'avg_completion_days' => $avgCompletionDays,
                'sla_rate' => $slaRate,
                'sla_compliant' => $slaCompliant,
                'sla_missed' => $slaMissed,
                'overdue_count' => $overdueCount,
            ];
        });

        // Department KPI summary
        $deptKpis = $departments->map(function (Department $dept) {
            $reqs = Requirement::where('department_id', $dept->id)->get();
            $total = $reqs->count();
            $completed = $reqs->where('is_completed', true)->count();
            $overdue = $reqs->filter(fn ($r) => $r->isOverdue())->count();
            $critical = $reqs->where('priority', 'critical')->where('is_completed', false)->count();
            $rate = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            return compact('dept', 'total', 'completed', 'overdue', 'critical', 'rate');
        })->sortByDesc('rate')->values();

        return view('admin.staff.index', compact(
            'staff', 'performance', 'departments', 'departmentId', 'search', 'deptKpis'
        ));
    }
}
