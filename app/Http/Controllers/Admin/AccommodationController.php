<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\PlanningWeek;
use App\Models\WeeklyAccommodation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccommodationController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function edit(PlanningWeek $planningWeek): View
    {
        $hostels = Hostel::where('is_active', true)->orderBy('display_order')->get();

        $accommodation = WeeklyAccommodation::where('planning_week_id', $planningWeek->id)
            ->with('hostel')
            ->get()
            ->keyBy('hostel_id');

        // Get previous week's data to prefill
        $previousWeek = PlanningWeek::where('week_start', '<', $planningWeek->week_start)
            ->orderByDesc('week_start')
            ->first();

        $previousData = collect();
        if ($previousWeek) {
            $previousData = WeeklyAccommodation::where('planning_week_id', $previousWeek->id)
                ->get()
                ->keyBy('hostel_id');
        }

        return view('admin.accommodation.edit', compact('planningWeek', 'hostels', 'accommodation', 'previousData'));
    }

    public function update(Request $request, PlanningWeek $planningWeek): RedirectResponse
    {
        $validated = $request->validate([
            'hostels' => 'required|array',
            'hostels.*.hostel_id' => 'required|exists:hostels,id',
            'hostels.*.occupancy' => 'required|integer|min:0',
            'hostels.*.rooms_out_of_order' => 'required|integer|min:0',
            'hostels.*.notes' => 'nullable|string',
            'outsourced_accommodation' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['hostels'] as $data) {
            WeeklyAccommodation::updateOrCreate(
                [
                    'planning_week_id' => $planningWeek->id,
                    'hostel_id' => $data['hostel_id'],
                ],
                [
                    'occupancy' => $data['occupancy'],
                    'rooms_out_of_order' => $data['rooms_out_of_order'],
                    'notes' => $data['notes'] ?? null,
                ]
            );
        }

        // Store outsourced accommodation count on the planning week
        $planningWeek->update([
            'outsourced_accommodation' => $validated['outsourced_accommodation'] ?? 0,
        ]);

        return redirect()
            ->route('admin.planning-weeks.show', $planningWeek)
            ->with('success', 'Accommodation data updated successfully.');
    }

    public function prefillFromPrevious(PlanningWeek $planningWeek): RedirectResponse
    {
        $previousWeek = PlanningWeek::where('week_start', '<', $planningWeek->week_start)
            ->orderByDesc('week_start')
            ->first();

        if (! $previousWeek) {
            return back()->with('error', 'No previous week data found to copy from.');
        }

        $previousData = WeeklyAccommodation::where('planning_week_id', $previousWeek->id)->get();

        if ($previousData->isEmpty()) {
            return back()->with('error', 'Previous week has no accommodation data.');
        }

        foreach ($previousData as $data) {
            WeeklyAccommodation::updateOrCreate(
                [
                    'planning_week_id' => $planningWeek->id,
                    'hostel_id' => $data->hostel_id,
                ],
                [
                    'occupancy' => $data->occupancy,
                    'rooms_out_of_order' => $data->rooms_out_of_order,
                    'notes' => $data->notes,
                ]
            );
        }

        if ($previousWeek->outsourced_accommodation) {
            $planningWeek->update([
                'outsourced_accommodation' => $previousWeek->outsourced_accommodation,
            ]);
        }

        return back()->with('success', "Accommodation data prefilled from {$previousWeek->label}.");
    }
}
