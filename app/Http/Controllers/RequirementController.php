<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\RequirementStatus;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\User;
use App\Notifications\RequirementAssignedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RequirementController extends Controller
{
    /**
     * Update the status of a requirement.
     */
    public function updateStatus(Request $request, Requirement $requirement): JsonResponse
    {
        $user = Auth::user();

        if ($user->isDepartmentScoped() && $requirement->department_id !== $user->department_id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(RequirementStatus::class)],
        ]);

        $oldStatus = $requirement->status;
        $newStatus = RequirementStatus::from($validated['status']);

        $requirement->setStatus($newStatus);

        AuditLog::create([
            'user_id' => $user->id,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'updated',
            'old_values' => ['status' => $oldStatus->value],
            'new_values' => ['status' => $newStatus->value],
        ]);

        $requirement->load('department');

        $event = $requirement->event()->with('requirements')->first();
        $dept = $requirement->department;
        $deptReqs = $event->requirements->where('department_id', $dept->id);

        $total = $deptReqs->count();
        $completedWeight = $deptReqs->sum(fn ($r) => $r->status->readinessWeight());
        $percentage = $total > 0 ? (int) round(($completedWeight / $total) * 100) : 0;

        $deptTotalW = $deptReqs->sum(fn ($r) => $r->priority->weight());
        $deptCompletedW = $deptReqs->sum(fn ($r) => $r->priority->weight() * $r->status->readinessWeight());
        $deptWeighted = $deptTotalW > 0 ? (int) round(($deptCompletedW / $deptTotalW) * 100) : 0;

        $allTotal = $event->requirements->count();
        $allCompletedWeight = $event->requirements->sum(fn ($r) => $r->status->readinessWeight());
        $overall = $allTotal > 0 ? (int) round(($allCompletedWeight / $allTotal) * 100) : 0;

        $allTotalW = $event->requirements->sum(fn ($r) => $r->priority->weight());
        $allCompletedW = $event->requirements->sum(fn ($r) => $r->priority->weight() * $r->status->readinessWeight());
        $overallWeighted = $allTotalW > 0 ? (int) round(($allCompletedW / $allTotalW) * 100) : 0;

        return response()->json([
            'status' => $requirement->status->value,
            'status_label' => $requirement->status->label(),
            'status_classes' => $requirement->status->classes(),
            'is_completed' => $requirement->status->isCompleted(),
            'completed_at' => $requirement->completed_at?->format('d M Y H:i'),
            'department_percentage' => $percentage,
            'department_weighted' => $deptWeighted,
            'department_status' => Department::ragStatus($percentage),
            'overall_percentage' => $overall,
            'overall_weighted' => $overallWeighted,
            'overall_status' => Department::ragStatus($overall),
            'critical_pending' => $event->requirements->where('priority.value', 'critical')->where('status', '!=', 'completed')->count(),
        ]);
    }

    /**
     * Assign a requirement to a staff member.
     */
    public function assign(Request $request, Requirement $requirement): RedirectResponse
    {
        $user = Auth::user();

        // HOD can only assign within their department
        abort_unless(
            $user->isAdmin() || ($user->isHod() && $requirement->department_id === $user->department_id),
            403,
            'You can only assign requirements in your department.'
        );

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $assignee = User::findOrFail($validated['assigned_to']);

        // Ensure assignee is in the same department
        abort_unless(
            $assignee->department_id === $requirement->department_id,
            403,
            'Staff member must be in the same department.'
        );

        $oldAssignee = $requirement->assigned_to;

        $requirement->update([
            'assigned_to' => $assignee->id,
            'responsible_officer' => $assignee->name,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'updated',
            'old_values' => ['assigned_to' => $oldAssignee],
            'new_values' => ['assigned_to' => $assignee->id, 'responsible_officer' => $assignee->name],
        ]);

        // Notify assignee
        $assignee->notify(new RequirementAssignedNotification($requirement, $user));

        return back()->with('success', 'Requirement assigned to '.$assignee->name.'.');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['required', 'string', 'max:500'],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'status' => ['nullable', Rule::enum(RequirementStatus::class)],
            'deadline' => ['nullable', 'date'],
            'responsible_officer' => ['nullable', 'string', 'max:255'],
            'requires_deliberation' => ['nullable', 'boolean'],
        ]);

        $status = $validated['status'] ?? RequirementStatus::NotStarted;

        $requirement = Requirement::create([
            'event_id' => $validated['event_id'],
            'department_id' => $validated['department_id'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? Priority::Medium->value,
            'status' => $status,
            'deadline' => $validated['deadline'] ?? null,
            'responsible_officer' => $validated['responsible_officer'] ?? null,
            'requires_deliberation' => $validated['requires_deliberation'] ?? false,
            'is_completed' => $status->isCompleted(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'created',
            'new_values' => $requirement->only(['description', 'priority', 'status', 'deadline', 'responsible_officer', 'requires_deliberation']),
        ]);

        return back()->with('success', 'Requirement added.');
    }

    public function destroy(Requirement $requirement): RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'deleted',
            'old_values' => $requirement->only(['description', 'priority', 'status', 'deadline', 'responsible_officer', 'is_completed']),
        ]);

        $requirement->delete();

        return back()->with('success', 'Requirement removed.');
    }

    /**
     * Escalate a requirement — admin or HOD only.
     */
    public function escalate(Requirement $requirement): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || ($user->isHod() && $requirement->department_id === $user->department_id),
            403
        );

        $oldStatus = $requirement->status;
        $requirement->escalate();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'escalated',
            'old_values' => ['status' => $oldStatus->value],
            'new_values' => ['status' => RequirementStatus::Escalated->value, 'escalated_at' => now()->toDateTimeString()],
        ]);

        return back()->with('success', 'Requirement escalated.');
    }
}
