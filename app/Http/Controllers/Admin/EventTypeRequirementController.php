<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EventTypeRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventTypeRequirementController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(Request $request): View
    {
        $selectedType = $request->string('type')->value();

        $eventTypes = EventTypeRequirement::distinct()->orderBy('event_type')->pluck('event_type');

        $templates = EventTypeRequirement::with('department')
            ->when($selectedType, fn ($q) => $q->where('event_type', $selectedType))
            ->orderBy('event_type')
            ->orderBy('department_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('event_type');

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.event-type-requirements.index', compact('templates', 'eventTypes', 'selectedType', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_type'    => ['required', 'string', 'max:100'],
            'department_id' => ['required', 'exists:departments,id'],
            'description'   => ['required', 'string', 'max:500'],
        ]);

        EventTypeRequirement::firstOrCreate(
            [
                'event_type'    => $validated['event_type'],
                'department_id' => $validated['department_id'],
                'description'   => $validated['description'],
            ],
            ['is_active' => true, 'sort_order' => 0]
        );

        return back()->with('success', 'Template requirement added to "'.$validated['event_type'].'".');
    }

    public function destroy(EventTypeRequirement $eventTypeRequirement): RedirectResponse
    {
        $type = $eventTypeRequirement->event_type;
        $eventTypeRequirement->delete();

        return back()->with('success', 'Template requirement removed from "'.$type.'".');
    }

    /** Toggle active/inactive without deleting. */
    public function toggle(EventTypeRequirement $eventTypeRequirement): RedirectResponse
    {
        $eventTypeRequirement->update(['is_active' => ! $eventTypeRequirement->is_active]);

        return back()->with('success', 'Template requirement '.($eventTypeRequirement->is_active ? 'enabled' : 'disabled').'.');
    }
}
