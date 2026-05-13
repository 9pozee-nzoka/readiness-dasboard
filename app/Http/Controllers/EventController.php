<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Event;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function show(Event $event): \Illuminate\View\View
    {
        $user = Auth::user();
        $event->load(['planningWeek', 'requirements.department']);

        $priorityFilter = request()->string('priority')->value();

        // Department-scoped users only see their own department
        $departments = Department::where('is_active', true)
            ->when($user->isDepartmentScoped(), fn ($q) => $q->where('id', $user->department_id))
            ->orderBy('name')
            ->get();

        $departmentReadiness = $departments->map(function (Department $dept) use ($event, $priorityFilter) {
            $requirements = $event->requirements
                ->where('department_id', $dept->id)
                ->when($priorityFilter, fn ($c) => $c->where('priority.value', $priorityFilter))
                ->sortBy(fn ($r) => $r->priority->sortOrder())
                ->values();

            // Always use full set for percentage calculations
            $allReqs   = $event->requirements->where('department_id', $dept->id);
            $total     = $allReqs->count();
            $completed = $allReqs->where('is_completed', true)->count();
            $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            // Weighted
            $totalW     = $allReqs->sum(fn ($r) => $r->priority->weight());
            $completedW = $allReqs->where('is_completed', true)->sum(fn ($r) => $r->priority->weight());
            $weighted   = $totalW > 0 ? (int) round(($completedW / $totalW) * 100) : 0;

            // Critical pending count for this dept
            $criticalPending = $allReqs->where('priority.value', 'critical')->where('is_completed', false)->count();

            return [
                'department'      => $dept,
                'requirements'    => $requirements,
                'total'           => $total,
                'completed'       => $completed,
                'percentage'      => $percentage,
                'weighted'        => $weighted,
                'criticalPending' => $criticalPending,
                'status'          => Department::ragStatus($percentage),
                'classes'         => Department::ragClasses($percentage),
            ];
        })->filter(fn ($d) => $d['total'] > 0)->values();

        $priorities = \App\Enums\Priority::cases();

        return view('events.show', compact('event', 'departmentReadiness', 'priorities', 'priorityFilter'));
    }

    public function create(): \Illuminate\View\View
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('events.create', compact('weeks', 'departments'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $validated = $request->validate([
            'planning_week_id' => ['required', 'exists:planning_weeks,id'],
            'name'             => ['required', 'string', 'max:255'],
            'type'             => ['required', 'string', 'max:100'],
            'event_date'       => ['required', 'date'],
            'event_time'       => ['nullable', 'date_format:H:i'],
            'venue'            => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
        ]);

        $event = Event::create($validated);

        // Auto-create requirement slots for all active departments
        $departments = Department::where('is_active', true)->get();
        foreach ($departments as $dept) {
            Requirement::create([
                'event_id'            => $event->id,
                'department_id'       => $dept->id,
                'description'         => 'Prepare department readiness report',
                'is_completed'        => false,
                'responsible_officer' => $dept->head_name,
            ]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created successfully.');
    }

    public function edit(Event $event): \Illuminate\View\View
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();

        return view('events.edit', compact('event', 'weeks'));
    }

    public function update(Request $request, Event $event): \Illuminate\Http\RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $validated = $request->validate([
            'planning_week_id' => ['required', 'exists:planning_weeks,id'],
            'name'             => ['required', 'string', 'max:255'],
            'type'             => ['required', 'string', 'max:100'],
            'event_date'       => ['required', 'date'],
            'event_time'       => ['nullable', 'date_format:H:i'],
            'venue'            => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
        ]);

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): \Illuminate\Http\RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $weekId = $event->planning_week_id;
        $event->delete();

        return redirect()->route('dashboard.index', ['week' => $weekId])
            ->with('success', 'Event deleted.');
    }

    public function exportCsv(Event $event): StreamedResponse
    {
        $event->load(['requirements.department']);

        $filename = 'readiness-'.str($event->name)->slug().'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($event) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Event', 'Department', 'Requirement', 'Status', 'Responsible Officer', 'Completed At']);

            foreach ($event->requirements->sortBy('department.name') as $req) {
                fputcsv($handle, [
                    $event->name,
                    $req->department->name,
                    $req->description,
                    $req->is_completed ? 'Completed' : 'Pending',
                    $req->responsible_officer ?? '—',
                    $req->completed_at?->format('d M Y H:i') ?? '—',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
