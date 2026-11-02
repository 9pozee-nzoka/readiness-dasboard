<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportWeeklyScheduleRequest;
use App\Http\Requests\Admin\StorePlanningWeekRequest;
use App\Mail\ScheduleShared;
use App\Models\Department;
use App\Models\Notification;
use App\Models\PlanningWeek;
use App\Models\User;
use App\Services\WeeklyScheduleImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanningWeekController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(): View
    {
        $weeks = PlanningWeek::query()
            ->withCount('events')
            ->orderByDesc('week_start')
            ->get();

        return view('admin.planning-weeks.index', compact('weeks'));
    }

    public function create(): View
    {
        return view('admin.planning-weeks.create');
    }

    public function store(StorePlanningWeekRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $week = DB::transaction(function () use ($validated) {
            if ($validated['is_current'] ?? false) {
                PlanningWeek::query()->update(['is_current' => false]);
            }

            return PlanningWeek::create($validated);
        });

        return redirect()
            ->route('admin.planning-weeks.show', $week)
            ->with('success', 'Planning week created.');
    }

    public function show(PlanningWeek $planningWeek): View
    {
        $planningWeek->load([
            'events' => fn ($query) => $query->withCount('requirements')->orderBy('event_date'),
        ]);

        return view('admin.planning-weeks.show', [
            'week' => $planningWeek,
        ]);
    }

    public function edit(PlanningWeek $planningWeek): View
    {
        return view('admin.planning-weeks.edit', [
            'week' => $planningWeek,
        ]);
    }

    public function update(StorePlanningWeekRequest $request, PlanningWeek $planningWeek): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $planningWeek) {
            if ($validated['is_current'] ?? false) {
                PlanningWeek::query()
                    ->whereKeyNot($planningWeek->id)
                    ->update(['is_current' => false]);
            }

            $planningWeek->update($validated);
        });

        return redirect()
            ->route('admin.planning-weeks.show', $planningWeek)
            ->with('success', 'Planning week updated.');
    }

    public function destroy(PlanningWeek $planningWeek): RedirectResponse
    {
        $planningWeek->delete();

        return redirect()
            ->route('admin.planning-weeks.index')
            ->with('success', 'Planning week and its events were removed.');
    }

    public function importForm(): View
    {
        return view('admin.planning-weeks.import', [
            'sampleSchedules' => [
                '2026-05-18' => '18–22 May 2026 (Nairobi campus)',
            ],
        ]);
    }

    public function import(ImportWeeklyScheduleRequest $request, WeeklyScheduleImportService $importer): RedirectResponse
    {
        try {
            if ($request->filled('sample_schedule')) {
                $path = database_path('data/weekly-schedule-'.$request->string('sample_schedule').'.json');
                $result = $importer->importFromJsonFile($path, $request->boolean('replace_existing'));
            } elseif ($request->hasFile('schedule_file')) {
                $path = $request->file('schedule_file')->getRealPath();
                $decoded = json_decode(file_get_contents($path), true);

                if (! is_array($decoded)) {
                    return back()->withErrors([
                        'schedule_file' => 'Upload a valid JSON file matching the KSG weekly schedule format.',
                    ]);
                }

                $result = $importer->importFromArray($decoded, $request->boolean('replace_existing'));
            } else {
                return back()->withErrors([
                    'schedule_file' => 'Choose a sample schedule or upload a JSON file.',
                ]);
            }
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'schedule_file' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.planning-weeks.show', $result['week'])
            ->with('success', "Imported {$result['events_created']} events ({$result['events_skipped']} skipped as duplicates).");
    }

    public function setCurrent(PlanningWeek $planningWeek): RedirectResponse
    {
        DB::transaction(function () use ($planningWeek) {
            PlanningWeek::query()->update(['is_current' => false]);
            $planningWeek->update(['is_current' => true]);
        });

        return back()->with('success', "{$planningWeek->label} is now the current planning week.");
    }

    public function share(Request $request, PlanningWeek $planningWeek): RedirectResponse
    {
        $request->validate([
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
        ]);

        $departments = Department::whereIn('id', $request->input('departments'))->get();
        $planningWeek->load('events');

        $sentCount = 0;

        foreach ($departments as $department) {
            // Get all users in this department (HODs and employees)
            $recipients = User::where('department_id', $department->id)
                ->whereIn('role', ['hod', 'employee'])
                ->where('is_approved', true)
                ->get();

            foreach ($recipients as $recipient) {
                Notification::notify(
                    $recipient->id,
                    Notification::TYPE_EVENT_UPDATED,
                    auth()->user()->name." shared the weekly schedule for {$planningWeek->label} with {$department->name}",
                    route('dashboard.index'),
                    null
                );

                // Send email if not a fake address
                if ($recipient->email && $this->isRealEmail($recipient->email)) {
                    \Mail::to($recipient->email)->send(
                        new ScheduleShared($planningWeek, $recipient, auth()->user(), $department->name)
                    );
                }

                $sentCount++;
            }
        }

        $deptCount = $departments->count();
        $message = "Schedule shared to {$deptCount} department(s) ({$sentCount} recipients).";

        return back()->with('success', $message);
    }

    public function shareAll(PlanningWeek $planningWeek): RedirectResponse
    {
        $departments = Department::where('is_active', true)->get();
        $planningWeek->load('events');

        $sentCount = 0;

        foreach ($departments as $department) {
            // Get all users in this department (HODs and employees)
            $recipients = User::where('department_id', $department->id)
                ->whereIn('role', ['hod', 'employee'])
                ->where('is_approved', true)
                ->get();

            foreach ($recipients as $recipient) {
                Notification::notify(
                    $recipient->id,
                    Notification::TYPE_EVENT_UPDATED,
                    auth()->user()->name." shared the weekly schedule for {$planningWeek->label} with {$department->name}",
                    route('dashboard.index'),
                    null
                );

                // Send email if not a fake address
                if ($recipient->email && $this->isRealEmail($recipient->email)) {
                    \Mail::to($recipient->email)->send(
                        new ScheduleShared($planningWeek, $recipient, auth()->user(), $department->name)
                    );
                }

                $sentCount++;
            }
        }

        $deptCount = $departments->count();
        $message = "Schedule shared to all {$deptCount} departments ({$sentCount} recipients).";

        return back()->with('success', $message);
    }

    /**
     * Check if email is real (not fake/seeded)
     */
    private function isRealEmail(string $email): bool
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

        return ! in_array(strtolower($email), $fakeEmails) &&
               ! str_contains(strtolower($email), '@example.com') &&
               ! str_contains(strtolower($email), '@test.com');
    }
}
