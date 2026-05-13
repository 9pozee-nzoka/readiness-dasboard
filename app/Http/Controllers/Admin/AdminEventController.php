<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventTypeRequirement;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class AdminEventController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(Request $request): View
    {
        $weeks = PlanningWeek::withCount('events')->orderBy('week_start', 'desc')->get();

        $selectedWeekId = $request->integer('week')
            ?: PlanningWeek::where('is_current', true)->value('id')
            ?: $weeks->first()?->id;

        $selectedWeek = $weeks->firstWhere('id', $selectedWeekId);

        $events = Event::with(['requirements', 'planningWeek'])
            ->where('planning_week_id', $selectedWeekId)
            ->orderBy('event_date')
            ->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.events.index', compact('weeks', 'selectedWeek', 'events', 'departments'));
    }

    public function create(): View
    {
        $weeks       = PlanningWeek::orderBy('week_start', 'desc')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // All distinct event types that have templates, plus any existing event types
        $templateTypes = EventTypeRequirement::distinct()->orderBy('event_type')->pluck('event_type');
        $existingTypes = Event::distinct()->orderBy('type')->pluck('type');
        $eventTypes    = $templateTypes->merge($existingTypes)->unique()->sort()->values();

        // Pre-load all templates grouped by type → department_id → [descriptions]
        $allTemplates = EventTypeRequirement::with('department')
            ->where('is_active', true)
            ->orderBy('department_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('event_type')
            ->map(fn ($items) => $items->groupBy('department_id'));

        return view('admin.events.create', compact('weeks', 'departments', 'eventTypes', 'allTemplates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'planning_week_id'              => ['required', 'exists:planning_weeks,id'],
            'name'                          => ['required', 'string', 'max:255'],
            'type'                          => ['required', 'string', 'max:100'],
            'event_date'                    => ['required', 'date'],
            'event_time'                    => ['nullable', 'date_format:H:i'],
            'venue'                         => ['nullable', 'string', 'max:255'],
            'description'                   => ['nullable', 'string'],
            // Selected template IDs with per-item priority/deadline overrides
            'template_ids'                  => ['nullable', 'array'],
            'template_ids.*'                => ['integer'],
            'template_priority'             => ['nullable', 'array'],
            'template_priority.*'           => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\Priority::class)],
            'template_deadline'             => ['nullable', 'array'],
            'template_deadline.*'           => ['nullable', 'date'],
            // Custom requirements
            'custom_reqs'                   => ['nullable', 'array'],
            'custom_reqs.*.department_id'   => ['required_with:custom_reqs', 'exists:departments,id'],
            'custom_reqs.*.description'     => ['required_with:custom_reqs', 'string', 'max:500'],
            'custom_reqs.*.priority'        => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\Priority::class)],
            'custom_reqs.*.deadline'        => ['nullable', 'date'],
            'custom_reqs.*.officer'         => ['nullable', 'string', 'max:255'],
        ]);

        $event = Event::create([
            'planning_week_id' => $validated['planning_week_id'],
            'name'             => $validated['name'],
            'type'             => $validated['type'],
            'event_date'       => $validated['event_date'],
            'event_time'       => $validated['event_time'] ?? null,
            'venue'            => $validated['venue'] ?? null,
            'description'      => $validated['description'] ?? null,
        ]);

        $eventDate = \Carbon\Carbon::parse($validated['event_date']);

        // Apply selected predefined templates with per-item priority/deadline
        $selectedIds      = $validated['template_ids'] ?? [];
        $templatePriority = $validated['template_priority'] ?? [];
        $templateDeadline = $validated['template_deadline'] ?? [];

        if (! empty($selectedIds)) {
            $templates = EventTypeRequirement::with('department')
                ->whereIn('id', $selectedIds)
                ->get();

            foreach ($templates as $template) {
                // Use the override if provided, otherwise fall back to template default
                $priority = $templatePriority[$template->id] ?? $template->priority->value;
                $deadline = $templateDeadline[$template->id] ?? $template->deadlineFor($eventDate)?->toDateString();

                Requirement::create([
                    'event_id'            => $event->id,
                    'department_id'       => $template->department_id,
                    'description'         => $template->description,
                    'priority'            => $priority,
                    'deadline'            => $deadline,
                    'is_completed'        => false,
                    'responsible_officer' => $template->department->head_name,
                ]);
            }
        }

        // Apply custom requirements and save them back to the template library
        foreach ($validated['custom_reqs'] ?? [] as $custom) {
            if (empty(trim($custom['description'] ?? ''))) {
                continue;
            }

            $priority = $custom['priority'] ?? \App\Enums\Priority::Medium->value;
            $deadline = $custom['deadline'] ?? null;

            Requirement::create([
                'event_id'            => $event->id,
                'department_id'       => $custom['department_id'],
                'description'         => $custom['description'],
                'priority'            => $priority,
                'deadline'            => $deadline,
                'is_completed'        => false,
                'responsible_officer' => $custom['officer'] ?? null,
            ]);

            // Remember in template library with priority
            EventTypeRequirement::remember(
                $validated['type'],
                (int) $custom['department_id'],
                $custom['description'],
                $priority,
            );
        }

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event created with '.$event->requirements()->count().' requirements.');
    }

    public function show(Event $event): View
    {
        $event->load(['planningWeek', 'requirements.department']);
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $departmentRequirements = $departments->map(function ($dept) use ($event) {
            $reqs      = $event->requirements->where('department_id', $dept->id)->values();
            $total     = $reqs->count();
            $completed = $reqs->where('is_completed', true)->count();
            $pct       = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            return compact('dept', 'reqs', 'total', 'completed', 'pct');
        });

        return view('admin.events.show', compact('event', 'departments', 'departmentRequirements'));
    }

    public function edit(Event $event): View
    {
        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();

        return view('admin.events.edit', compact('event', 'weeks'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
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

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $weekId = $event->planning_week_id;
        $event->delete();

        return redirect()->route('admin.events.index', ['week' => $weekId])
            ->with('success', 'Event deleted.');
    }

    /**
     * Add a requirement to a department for this event.
     * Also saves it back to the template library for this event type.
     */
    public function addRequirement(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'department_id'       => ['required', 'exists:departments,id'],
            'description'         => ['required', 'string', 'max:500'],
            'priority'            => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\Priority::class)],
            'deadline'            => ['nullable', 'date'],
            'responsible_officer' => ['nullable', 'string', 'max:255'],
        ]);

        $priority = $validated['priority'] ?? \App\Enums\Priority::Medium->value;

        Requirement::create([
            'event_id'            => $event->id,
            'department_id'       => $validated['department_id'],
            'description'         => $validated['description'],
            'priority'            => $priority,
            'deadline'            => $validated['deadline'] ?? null,
            'responsible_officer' => $validated['responsible_officer'] ?? null,
            'is_completed'        => false,
        ]);

        // Save to template library so future events of this type inherit it
        EventTypeRequirement::remember($event->type, (int) $validated['department_id'], $validated['description'], $priority);

        return back()->with('success', 'Requirement added and saved to the "'.$event->type.'" template library.');
    }

    /** Remove a requirement from this event only (does not touch the template library). */
    public function removeRequirement(Event $event, Requirement $requirement): RedirectResponse
    {
        abort_unless($requirement->event_id === $event->id, 404);
        $requirement->delete();

        return back()->with('success', 'Requirement removed.');
    }
}
