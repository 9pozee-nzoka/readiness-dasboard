<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\RequirementStatus;
use App\Mail\RequirementAssigned;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Requirement;
use App\Models\User;
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
     * Update the priority of a requirement — HOD only for their department.
     */
    public function updatePriority(Request $request, Requirement $requirement): RedirectResponse
    {
        $user = Auth::user();

        // Only HODs can update priorities in their department
        abort_unless(
            $user->isHod() && $requirement->department_id === $user->department_id,
            403,
            'Only the HOD can update requirement priorities in this department.'
        );

        $validated = $request->validate([
            'priority' => ['required', Rule::enum(Priority::class)],
        ]);

        $oldPriority = $requirement->priority;
        $newPriority = Priority::from($validated['priority']);

        $requirement->update([
            'priority' => $newPriority->value,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'updated',
            'old_values' => ['priority' => $oldPriority->value],
            'new_values' => ['priority' => $newPriority->value],
        ]);

        return back()->with('success', 'Requirement priority updated to '.$newPriority->label().'.');
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

        // Notify assignee using custom notification system
        Notification::notify(
            $assignee->id,
            Notification::TYPE_ASSIGNMENT,
            "You have been assigned: {$requirement->description}",
            route('events.show', $requirement->event_id),
            $requirement->event_id,
            $requirement->id
        );

        // Send email notification
        if ($assignee->email && ! $this->isFakeEmail($assignee->email)) {
            \Mail::to($assignee->email)->send(
                new RequirementAssigned($requirement->load(['event', 'department']), $assignee, $user)
            );
        }

        return back()->with('success', 'Requirement assigned to '.$assignee->name.'.');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['required', 'string', 'max:500'],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'status' => ['nullable', Rule::enum(RequirementStatus::class)],
            'deadline' => ['nullable', 'date'],
            'responsible_officer' => ['nullable', 'string', 'max:255'],
        ]);

        // Authorization: Allow admins, course coordinators, programme assistants, and HODs (for their own department only)
        $canAdd = $user->canManageEvents() ||
                  ($user->isHod() && $user->department_id == $validated['department_id']);

        abort_unless($canAdd, 403, 'You can only add requirements for your own department.');

        $status = $validated['status'] ?? RequirementStatus::NotStarted;

        $requirement = Requirement::create([
            'event_id' => $validated['event_id'],
            'department_id' => $validated['department_id'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? Priority::Medium->value,
            'status' => $status,
            'deadline' => $validated['deadline'] ?? null,
            'responsible_officer' => $validated['responsible_officer'] ?? null,
            'is_completed' => $status->isCompleted(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'created',
            'new_values' => $requirement->only(['description', 'priority', 'status', 'deadline', 'responsible_officer']),
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

        $requirement->escalate();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'escalated',
            'old_values' => ['is_escalated' => false],
            'new_values' => ['is_escalated' => true, 'escalated_at' => now()->toDateTimeString()],
        ]);

        return back()->with('success', 'Requirement escalated.');
    }

    /**
     * Verify a completed requirement — HOD only for their department.
     */
    public function verify(Request $request, Requirement $requirement): RedirectResponse
    {
        $user = Auth::user();

        // Only HODs can verify requirements in their department
        abort_unless(
            $user->isHod() && $requirement->department_id === $user->department_id,
            403,
            'Only the HOD can verify requirements in this department.'
        );

        // Requirement must be completed before verification
        abort_unless(
            $requirement->is_completed,
            400,
            'Requirement must be completed before verification.'
        );

        $validated = $request->validate([
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $requirement->verify($user, $validated['verification_notes'] ?? null);

        AuditLog::create([
            'user_id' => $user->id,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'verified',
            'new_values' => [
                'is_verified' => true,
                'verified_by' => $user->id,
                'verified_at' => now()->toDateTimeString(),
                'verification_notes' => $validated['verification_notes'] ?? null,
            ],
        ]);

        return back()->with('success', 'Requirement verified successfully.');
    }

    /**
     * Unverify a requirement — HOD only for their department.
     */
    public function unverify(Requirement $requirement): RedirectResponse
    {
        $user = Auth::user();

        // Only HODs can unverify requirements in their department
        abort_unless(
            $user->isHod() && $requirement->department_id === $user->department_id,
            403,
            'Only the HOD can unverify requirements in this department.'
        );

        $requirement->unverify();

        AuditLog::create([
            'user_id' => $user->id,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'unverified',
            'old_values' => [
                'is_verified' => true,
                'verified_by' => $requirement->verified_by,
                'verified_at' => $requirement->verified_at?->toDateTimeString(),
            ],
            'new_values' => ['is_verified' => false],
        ]);

        return back()->with('success', 'Requirement verification removed.');
    }

    /**
     * Check if email is fake/seeded
     */
    private function isFakeEmail(string $email): bool
    {
        $fakeEmails = [
            'ict-hod@ksg.ac.ke',
            'finance-hod@ksg.ac.ke',
            'administration-hod@ksg.ac.ke',
            'security-hod@ksg.ac.ke',
            'logistics-hod@ksg.ac.ke',
            'communications-hod@ksg.ac.ke',
            'facilities-hod@ksg.ac.ke',
            'human-resources-hod@ksg.ac.ke',
            'protocol-hod@ksg.ac.ke',
            'catering-hod@ksg.ac.ke',
            'kevin.omondi@ksg.ac.ke',
            'sarah.achieng@ksg.ac.ke',
            'paul.kiptoo@ksg.ac.ke',
            'director@ksg.ac.ke',
        ];

        return in_array(strtolower($email), $fakeEmails) ||
               str_contains(strtolower($email), '@example.com') ||
               str_contains(strtolower($email), '@test.com');
    }

    /**
     * Acknowledge a requirement via signed email link
     */
    public function acknowledge(Request $request, Requirement $requirement): RedirectResponse
    {
        $userId = $request->integer('user');
        $user = User::findOrFail($userId);

        // Verify the user is assigned to this requirement or is in the same department
        abort_unless(
            $requirement->assigned_to === $userId || $requirement->department_id === $user->department_id,
            403,
            'You are not authorized to acknowledge this requirement.'
        );

        // Update status to In Progress if it's Not Started
        if ($requirement->status === RequirementStatus::NotStarted) {
            $requirement->setStatus(RequirementStatus::InProgress);

            AuditLog::create([
                'user_id' => $userId,
                'event_id' => $requirement->event_id,
                'requirement_id' => $requirement->id,
                'action' => 'acknowledged',
                'old_values' => ['status' => RequirementStatus::NotStarted->value],
                'new_values' => ['status' => RequirementStatus::InProgress->value, 'acknowledged_via_email' => true],
            ]);
        }

        return redirect()->route('events.show', $requirement->event_id)
            ->with('success', 'Requirement acknowledged. Status updated to In Progress.');
    }

    /**
     * Mark a requirement as complete via signed email link
     */
    public function quickComplete(Request $request, Requirement $requirement): RedirectResponse
    {
        $userId = $request->integer('user');
        $user = User::findOrFail($userId);

        // Verify the user is assigned to this requirement or is in the same department
        abort_unless(
            $requirement->assigned_to === $userId || $requirement->department_id === $user->department_id,
            403,
            'You are not authorized to complete this requirement.'
        );

        $oldStatus = $requirement->status;
        $requirement->setStatus(RequirementStatus::Completed);

        AuditLog::create([
            'user_id' => $userId,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'completed',
            'old_values' => ['status' => $oldStatus->value],
            'new_values' => ['status' => RequirementStatus::Completed->value, 'completed_via_email' => true],
        ]);

        // Notify HOD about completion
        if ($user->department && $user->department->head_user_id) {
            Notification::notify(
                $user->department->head_user_id,
                Notification::TYPE_STATUS_CHANGED,
                "{$user->name} completed requirement: {$requirement->description}",
                route('events.show', $requirement->event_id),
                $requirement->event_id,
                $requirement->id,
                null,
                'Requirement Completed'
            );
        }

        return redirect()->route('events.show', $requirement->event_id)
            ->with('success', 'Requirement marked as complete!');
    }

    /**
     * Escalate a requirement via signed email link (with reason prompt)
     */
    public function quickEscalate(Request $request, Requirement $requirement): RedirectResponse
    {
        $userId = $request->integer('user');
        $user = User::findOrFail($userId);

        // Verify the user is assigned to this requirement or is in the same department
        abort_unless(
            $requirement->assigned_to === $userId || $requirement->department_id === $user->department_id,
            403,
            'You are not authorized to escalate this requirement.'
        );

        // Mark as escalated
        $requirement->update([
            'is_escalated' => true,
            'escalated_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $userId,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
            'action' => 'escalated',
            'old_values' => ['is_escalated' => false],
            'new_values' => ['is_escalated' => true, 'escalated_at' => now()->toDateTimeString(), 'escalated_via_email' => true],
        ]);

        // Notify HOD and Admin about escalation
        if ($user->department && $user->department->head_user_id) {
            Notification::notify(
                $user->department->head_user_id,
                Notification::TYPE_ESCALATED,
                "{$user->name} needs help with: {$requirement->description}",
                route('events.show', $requirement->event_id),
                $requirement->event_id,
                $requirement->id,
                null,
                'Requirement Escalated - Needs Assistance'
            );
        }

        // Notify admin as well
        $admins = User::where('role', 'admin')->where('is_approved', true)->get();
        foreach ($admins as $admin) {
            Notification::notify(
                $admin->id,
                Notification::TYPE_ESCALATED,
                "{$user->name} escalated requirement: {$requirement->description} in {$requirement->department->name}",
                route('events.show', $requirement->event_id),
                $requirement->event_id,
                $requirement->id,
                null,
                'Requirement Escalated'
            );
        }

        return redirect()->route('events.show', $requirement->event_id)
            ->with('success', 'Requirement escalated. Your HOD and administrators have been notified.');
    }
}
