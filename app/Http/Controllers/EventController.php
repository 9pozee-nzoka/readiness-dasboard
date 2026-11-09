<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\UserRole;
use App\Mail\EventCreatedNotification;
use App\Models\Department;
use App\Models\Event;
use App\Models\Notification;
use App\Models\PlanningWeek;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function show(Event $event): View
    {
        $user = Auth::user();
        $event->load(['planningWeek', 'requirements.department']);

        $priorityFilter = request()->string('priority')->value();

        // Department-scoped users only see their own department
        $departments = Department::where('is_active', true)
            ->when($user->isDepartmentScoped(), fn ($q) => $q->where('id', $user->department_id))
            ->orderBy('name')
            ->get();

        $departmentReadiness = $departments->map(function (Department $dept) use ($event, $priorityFilter, $user) {
            // For employees (staff), only show requirements assigned to them
            $requirements = $event->requirements
                ->where('department_id', $dept->id)
                ->when($user->isEmployee(), fn ($c) => $c->where('assigned_to', $user->id))
                ->when($priorityFilter, fn ($c) => $c->where('priority.value', $priorityFilter))
                ->sortBy(fn ($r) => $r->priority->sortOrder())
                ->values();

            // For percentage calculations, use filtered set for employees, full set for others
            $allReqs = $event->requirements
                ->where('department_id', $dept->id)
                ->when($user->isEmployee(), fn ($c) => $c->where('assigned_to', $user->id));

            $total = $allReqs->count();
            $completed = $allReqs->where('is_completed', true)->count();
            $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            // Weighted
            $totalW = $allReqs->sum(fn ($r) => $r->priority->weight());
            $completedW = $allReqs->where('is_completed', true)->sum(fn ($r) => $r->priority->weight());
            $weighted = $totalW > 0 ? (int) round(($completedW / $totalW) * 100) : 0;

            // Critical pending count for this dept
            $criticalPending = $allReqs->where('priority.value', 'critical')->where('is_completed', false)->count();

            return [
                'department' => $dept,
                'requirements' => $requirements,
                'total' => $total,
                'completed' => $completed,
                'percentage' => $percentage,
                'weighted' => $weighted,
                'criticalPending' => $criticalPending,
                'status' => Department::ragStatus($percentage),
                'classes' => Department::ragClasses($percentage),
            ];
        })->filter(function ($d) use ($user) {
            // Show departments with requirements, OR show HOD's own department even if empty
            return $d['total'] > 0 || ($user->isHod() && $user->department_id === $d['department']->id);
        })->values();

        $priorities = Priority::cases();

        return view('events.show', compact('event', 'departmentReadiness', 'priorities', 'priorityFilter'));
    }

    public function printReport(Event $event): View
    {
        $event->load(['planningWeek', 'requirements.department']);

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $departmentReadiness = $departments->map(function (Department $dept) use ($event) {
            $requirements = $event->requirements
                ->where('department_id', $dept->id)
                ->sortBy(fn ($r) => $r->priority->sortOrder())
                ->values();

            $allReqs = $event->requirements->where('department_id', $dept->id);
            $total = $allReqs->count();
            $completed = $allReqs->where('is_completed', true)->count();
            $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            $totalW = $allReqs->sum(fn ($r) => $r->priority->weight());
            $completedW = $allReqs->where('is_completed', true)->sum(fn ($r) => $r->priority->weight());
            $weighted = $totalW > 0 ? (int) round(($completedW / $totalW) * 100) : 0;

            $criticalPending = $allReqs->where('priority.value', 'critical')->where('is_completed', false)->count();

            return [
                'department' => $dept,
                'requirements' => $requirements,
                'total' => $total,
                'completed' => $completed,
                'percentage' => $percentage,
                'weighted' => $weighted,
                'criticalPending' => $criticalPending,
                'status' => Department::ragStatus($percentage),
                'classes' => Department::ragClasses($percentage),
            ];
        })->filter(fn ($d) => $d['total'] > 0)->values();

        return view('events.print-report', compact('event', 'departmentReadiness'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('events.create', compact('weeks', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $validated = $request->validate([
            'planning_week_id' => ['required', 'exists:planning_weeks,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $event = Event::create($validated);

        // Notify all HODs about the new event
        $hods = User::where('role', UserRole::Hod->value)
            ->where('is_approved', true)
            ->get();

        foreach ($hods as $hod) {
            // In-app notification
            Notification::notify(
                $hod->id,
                Notification::TYPE_EVENT_CREATED,
                Auth::user()->name." created a new event: {$event->name} on ".$event->event_date->format('d M Y'),
                route('events.show', $event),
                $event->id,
                null,
                null,
                'New Event Created'
            );

            // Email notification
            Mail::to($hod->email)->send(
                new EventCreatedNotification($event, Auth::user())
            );
        }

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event created. Add department requirements from the admin event page.');
    }

    public function edit(Event $event): View
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();

        return view('events.edit', compact('event', 'weeks'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        abort_unless(Auth::user()->canManageEvents(), 403);

        $validated = $request->validate([
            'planning_week_id' => ['required', 'exists:planning_weeks,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
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
