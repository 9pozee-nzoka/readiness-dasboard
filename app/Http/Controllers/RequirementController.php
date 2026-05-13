<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Models\Requirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RequirementController extends Controller
{
    /**
     * Toggle the completion status of a requirement.
     */
    public function toggle(Requirement $requirement): JsonResponse
    {
        $user = Auth::user();

        if ($user->isDepartmentScoped() && $requirement->department_id !== $user->department_id) {
            abort(403);
        }

        if ($requirement->is_completed) {
            $requirement->markPending();
        } else {
            $requirement->markCompleted();
        }

        $requirement->load('department');

        $event    = $requirement->event()->with('requirements')->first();
        $dept     = $requirement->department;
        $deptReqs = $event->requirements->where('department_id', $dept->id);

        $total      = $deptReqs->count();
        $completed  = $deptReqs->where('is_completed', true)->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Weighted dept readiness
        $deptTotalW     = $deptReqs->sum(fn ($r) => $r->priority->weight());
        $deptCompletedW = $deptReqs->where('is_completed', true)->sum(fn ($r) => $r->priority->weight());
        $deptWeighted   = $deptTotalW > 0 ? (int) round(($deptCompletedW / $deptTotalW) * 100) : 0;

        // Overall
        $allTotal     = $event->requirements->count();
        $allCompleted = $event->requirements->where('is_completed', true)->count();
        $overall      = $allTotal > 0 ? (int) round(($allCompleted / $allTotal) * 100) : 0;

        // Weighted overall
        $allTotalW     = $event->requirements->sum(fn ($r) => $r->priority->weight());
        $allCompletedW = $event->requirements->where('is_completed', true)->sum(fn ($r) => $r->priority->weight());
        $overallWeighted = $allTotalW > 0 ? (int) round(($allCompletedW / $allTotalW) * 100) : 0;

        return response()->json([
            'is_completed'           => $requirement->is_completed,
            'completed_at'           => $requirement->completed_at?->format('d M Y H:i'),
            'department_percentage'  => $percentage,
            'department_weighted'    => $deptWeighted,
            'department_status'      => \App\Models\Department::ragStatus($percentage),
            'overall_percentage'     => $overall,
            'overall_weighted'       => $overallWeighted,
            'overall_status'         => \App\Models\Department::ragStatus($overall),
            'critical_pending'       => $event->requirements->where('priority->value', 'critical')->where('is_completed', false)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $validated = $request->validate([
            'event_id'            => ['required', 'exists:events,id'],
            'department_id'       => ['required', 'exists:departments,id'],
            'description'         => ['required', 'string', 'max:500'],
            'priority'            => ['nullable', Rule::enum(Priority::class)],
            'deadline'            => ['nullable', 'date'],
            'responsible_officer' => ['nullable', 'string', 'max:255'],
        ]);

        Requirement::create([
            'event_id'            => $validated['event_id'],
            'department_id'       => $validated['department_id'],
            'description'         => $validated['description'],
            'priority'            => $validated['priority'] ?? Priority::Medium->value,
            'deadline'            => $validated['deadline'] ?? null,
            'responsible_officer' => $validated['responsible_officer'] ?? null,
            'is_completed'        => false,
        ]);

        return back()->with('success', 'Requirement added.');
    }

    public function destroy(Requirement $requirement): RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $requirement->delete();

        return back()->with('success', 'Requirement removed.');
    }

    /**
     * Escalate a requirement — admin only.
     */
    public function escalate(Requirement $requirement): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $requirement->escalate();

        return back()->with('success', 'Requirement escalated.');
    }
}
