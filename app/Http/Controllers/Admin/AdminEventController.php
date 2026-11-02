<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Priority;
use App\Enums\ScheduleCategory;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventAccommodation;
use App\Models\EventTypeRequirement;
use App\Models\Hostel;
use App\Models\Notification;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use App\Models\User;
use App\Models\WeeklyAccommodation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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

        $search = $request->string('search')->trim()->value();
        $type = $request->string('type')->trim()->value();
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        $events = Event::with(['requirements', 'planningWeek'])
            ->where('planning_week_id', $selectedWeekId)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($dateFrom, fn ($q) => $q->where('event_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('event_date', '<=', $dateTo))
            ->orderBy('event_date')
            ->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $eventTypes = Event::distinct()->orderBy('type')->pluck('type');

        return view('admin.events.index', compact(
            'weeks', 'selectedWeek', 'events', 'departments',
            'search', 'type', 'dateFrom', 'dateTo', 'eventTypes'
        ));
    }

    public function create(Request $request): View
    {
        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();
        $selectedWeekId = $request->integer('planning_week')
            ?: PlanningWeek::where('is_current', true)->value('id');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $scheduleCategories = ScheduleCategory::options();
        $hostels = Hostel::where('is_active', true)->orderBy('display_order')->orderBy('name')->get();

        // All distinct event types that have templates, plus any existing event types
        $templateTypes = EventTypeRequirement::distinct()->orderBy('event_type')->pluck('event_type');
        $existingTypes = Event::distinct()->orderBy('type')->pluck('type');
        $eventTypes = $templateTypes->merge($existingTypes)->unique()->sort()->values();

        // Pre-load all templates grouped by type → department_id → [descriptions]
        $allTemplates = EventTypeRequirement::with('department')
            ->where('is_active', true)
            ->orderBy('department_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('event_type')
            ->map(fn ($items) => $items->groupBy('department_id'));

        return view('admin.events.create', compact(
            'weeks',
            'selectedWeekId',
            'departments',
            'eventTypes',
            'allTemplates',
            'scheduleCategories',
            'hostels',
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function scheduleFieldRules(): array
    {
        return [
            'schedule_category' => ['nullable', Rule::enum(ScheduleCategory::class)],
            'client' => ['nullable', 'string', 'max:255'],
            'event_date_end' => ['nullable', 'date', 'after_or_equal:event_date'],
            'participants_residential' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'participants_non_residential' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'coordinator' => ['nullable', 'string', 'max:255'],
            'programme_assistant' => ['nullable', 'string', 'max:255'],
            'package_remarks' => ['nullable', 'string', 'max:255'],
            'schedule_reference' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function scheduleAttributes(array $validated): array
    {
        return [
            'schedule_category' => $validated['schedule_category'] ?? null,
            'client' => $validated['client'] ?? null,
            'event_date_end' => $validated['event_date_end'] ?? null,
            'participants_residential' => $validated['participants_residential'] ?? null,
            'participants_non_residential' => $validated['participants_non_residential'] ?? null,
            'coordinator' => $validated['coordinator'] ?? null,
            'programme_assistant' => $validated['programme_assistant'] ?? null,
            'package_remarks' => $validated['package_remarks'] ?? null,
            'schedule_reference' => $validated['schedule_reference'] ?? null,
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'planning_week_id' => ['required', 'exists:planning_weeks,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            ...$this->scheduleFieldRules(),
            // Selected template IDs with per-item priority/deadline overrides
            'template_ids' => ['nullable', 'array'],
            'template_ids.*' => ['integer'],
            'template_priority' => ['nullable', 'array'],
            'template_priority.*' => ['nullable', Rule::enum(Priority::class)],
            'template_deadline' => ['nullable', 'array'],
            'template_deadline.*' => ['nullable', 'date'],
            // Custom requirements
            'custom_reqs' => ['nullable', 'array'],
            'custom_reqs.*.department_id' => ['required_with:custom_reqs', 'exists:departments,id'],
            'custom_reqs.*.description' => ['required_with:custom_reqs', 'string', 'max:500'],
            'custom_reqs.*.priority' => ['nullable', Rule::enum(Priority::class)],
            'custom_reqs.*.deadline' => ['nullable', 'date'],
            'custom_reqs.*.officer' => ['nullable', 'string', 'max:255'],
            // Accommodation bookings
            'accommodation_bookings' => ['nullable', 'array'],
            'accommodation_bookings.*.hostel_id' => ['required_with:accommodation_bookings', 'exists:hostels,id'],
            'accommodation_bookings.*.residency_type' => ['required_with:accommodation_bookings', 'string', 'in:Executive,Deluxe,Standard'],
            'accommodation_bookings.*.rooms_booked' => ['required_with:accommodation_bookings', 'integer', 'min:0'],
            'accommodation_bookings.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $event = Event::create([
            'planning_week_id' => $validated['planning_week_id'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'] ?? null,
            'venue' => $validated['venue'] ?? null,
            'description' => $validated['description'] ?? null,
            ...$this->scheduleAttributes($validated),
        ]);

        $selectedIds = $validated['template_ids'] ?? [];
        $templatePriority = $validated['template_priority'] ?? [];
        $templateDeadline = $validated['template_deadline'] ?? [];

        if (! empty($selectedIds)) {
            $templates = EventTypeRequirement::with('department')
                ->whereIn('id', $selectedIds)
                ->get();

            foreach ($templates as $template) {
                // Use the override if provided, otherwise fall back to template's deadline
                $priority = $templatePriority[$template->id] ?? $template->priority->value;
                $deadline = $templateDeadline[$template->id] ?? $template->deadline?->toDateString();

                Requirement::create([
                    'event_id' => $event->id,
                    'department_id' => $template->department_id,
                    'description' => $template->description,
                    'priority' => $priority,
                    'deadline' => $deadline,
                    'is_completed' => false,
                    'responsible_officer' => $template->department->head_name,
                ]);
            }
        }

        // Apply custom requirements and save them back to the template library
        foreach ($validated['custom_reqs'] ?? [] as $custom) {
            if (empty(trim($custom['description'] ?? ''))) {
                continue;
            }

            $priority = $custom['priority'] ?? Priority::Medium->value;
            $deadline = $custom['deadline'] ?? null;

            Requirement::create([
                'event_id' => $event->id,
                'department_id' => $custom['department_id'],
                'description' => $custom['description'],
                'priority' => $priority,
                'deadline' => $deadline,
                'is_completed' => false,
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

        // Handle accommodation bookings and track remaining rooms
        foreach ($validated['accommodation_bookings'] ?? [] as $booking) {
            if (empty($booking['rooms_booked']) || $booking['rooms_booked'] <= 0) {
                continue;
            }

            $hostel = Hostel::find($booking['hostel_id']);
            $planningWeek = $event->planningWeek;

            // Get or create weekly accommodation record
            $weeklyAccommodation = WeeklyAccommodation::firstOrCreate(
                [
                    'planning_week_id' => $planningWeek->id,
                    'hostel_id' => $hostel->id,
                ],
                [
                    'occupancy' => 0,
                    'rooms_out_of_order' => 0,
                ]
            );

            // Calculate available rooms before booking
            $availableBeforeBooking = $hostel->capacity - $weeklyAccommodation->occupancy - $weeklyAccommodation->rooms_out_of_order;

            // Update occupancy
            $weeklyAccommodation->occupancy += $booking['rooms_booked'];
            $weeklyAccommodation->save();

            // Calculate available rooms after booking
            $availableAfterBooking = $hostel->capacity - $weeklyAccommodation->occupancy - $weeklyAccommodation->rooms_out_of_order;

            // Determine if outsourcing is needed
            $requiresOutsourcing = $availableAfterBooking < 0 || $booking['rooms_booked'] > $availableBeforeBooking;

            // Create event accommodation record with logging
            EventAccommodation::create([
                'event_id' => $event->id,
                'hostel_id' => $hostel->id,
                'residency_type' => $booking['residency_type'],
                'rooms_booked' => $booking['rooms_booked'],
                'rooms_available_after_booking' => max(0, $availableAfterBooking),
                'requires_outsourcing' => $requiresOutsourcing,
                'notes' => $booking['notes'] ?? null,
            ]);
        }

        // Notify all HODs about the new event
        $hods = User::where('role', UserRole::Hod->value)
            ->where('is_approved', true)
            ->get();

        foreach ($hods as $hod) {
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
        }

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event created with '.$event->requirements()->count().' requirements.');
    }

    public function show(Event $event): View
    {
        $event->load(['planningWeek', 'requirements.department', 'accommodations.hostel']);
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $departmentRequirements = $departments->map(function ($dept) use ($event) {
            $reqs = $event->requirements->where('department_id', $dept->id)->values();
            $total = $reqs->count();
            $completed = $reqs->where('is_completed', true)->count();
            $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            return compact('dept', 'reqs', 'total', 'completed', 'pct');
        });

        return view('admin.events.show', compact('event', 'departments', 'departmentRequirements'));
    }

    public function edit(Event $event): View
    {
        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();
        $scheduleCategories = ScheduleCategory::options();

        return view('admin.events.edit', compact('event', 'weeks', 'scheduleCategories'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'planning_week_id' => ['required', 'exists:planning_weeks,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            ...$this->scheduleFieldRules(),
        ]);

        $event->update([
            ...$validated,
            ...$this->scheduleAttributes($validated),
        ]);

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
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['required', 'string', 'max:500'],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'deadline' => ['nullable', 'date'],
            'responsible_officer' => ['nullable', 'string', 'max:255'],
        ]);

        $priority = $validated['priority'] ?? Priority::Medium->value;

        $requirement = Requirement::create([
            'event_id' => $event->id,
            'department_id' => $validated['department_id'],
            'description' => $validated['description'],
            'priority' => $priority,
            'deadline' => $validated['deadline'] ?? null,
            'responsible_officer' => $validated['responsible_officer'] ?? null,
            'is_completed' => false,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'requirement_id' => $requirement->id,
            'action' => 'created',
            'new_values' => $requirement->only(['description', 'priority', 'deadline', 'responsible_officer']),
        ]);

        // Save to template library so future events of this type inherit it
        EventTypeRequirement::remember($event->type, (int) $validated['department_id'], $validated['description'], $priority);

        return back()->with('success', 'Requirement added and saved to the "'.$event->type.'" template library.');
    }

    /** Remove a requirement from this event only (does not touch the template library). */
    public function removeRequirement(Event $event, Requirement $requirement): RedirectResponse
    {
        abort_unless($requirement->event_id === $event->id, 404);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'requirement_id' => $requirement->id,
            'action' => 'deleted',
            'old_values' => $requirement->only(['description', 'priority', 'deadline', 'responsible_officer', 'is_completed']),
        ]);

        $requirement->delete();

        return back()->with('success', 'Requirement removed.');
    }
}
