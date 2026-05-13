<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Models\Department;
use App\Models\Event;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $user  = Auth::user();
        $weeks = PlanningWeek::orderBy('week_start', 'desc')->get();

        $selectedWeekId = $request->integer('week') ?: PlanningWeek::where('is_current', true)->value('id');

        if (! $selectedWeekId) {
            $selectedWeekId = $weeks->first()?->id;
        }

        $selectedWeek = $weeks->firstWhere('id', $selectedWeekId);

        $events = Event::with(['requirements'])
            ->where('planning_week_id', $selectedWeekId)
            ->orderBy('event_date')
            ->get();

        $departments = Department::where('is_active', true)
            ->when($user->isDepartmentScoped(), fn ($q) => $q->where('id', $user->department_id))
            ->orderBy('name')
            ->get();

        // Summary metrics
        $totalEvents      = $events->count();
        $fullyReady       = $events->filter(fn ($e) => $e->overallReadiness() === 100)->count();
        $inProgress       = $events->filter(fn ($e) => $e->overallReadiness() > 0 && $e->overallReadiness() < 100)->count();
        $notStarted       = $events->filter(fn ($e) => $e->overallReadiness() === 0)->count();
        $averageReadiness = $totalEvents > 0
            ? (int) round($events->avg(fn ($e) => $e->overallReadiness()))
            : 0;

        // Critical alerts — unresolved critical requirements across all events this week
        // Scoped to user's department if HOD/employee
        $criticalAlertsQuery = Requirement::with(['event', 'department'])
            ->whereIn('event_id', $events->pluck('id'))
            ->where('priority', Priority::Critical->value)
            ->where('is_completed', false)
            ->orderBy('deadline')
            ->byPriority();

        if ($user->isDepartmentScoped()) {
            $criticalAlertsQuery->where('department_id', $user->department_id);
        }

        $criticalAlerts = $criticalAlertsQuery->limit(10)->get();

        // Overdue requirements count
        $overdueCount = Requirement::whereIn('event_id', $events->pluck('id'))
            ->where('is_completed', false)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->when($user->isDepartmentScoped(), fn ($q) => $q->where('department_id', $user->department_id))
            ->count();

        return view('dashboard.index', compact(
            'weeks', 'selectedWeek', 'events', 'departments',
            'totalEvents', 'fullyReady', 'inProgress', 'notStarted', 'averageReadiness',
            'criticalAlerts', 'overdueCount',
        ));
    }
}
