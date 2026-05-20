<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Models\Department;
use App\Models\Event;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
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
        $totalEvents = $events->count();
        $fullyReady = $events->filter(fn ($e) => $e->overallReadiness() === 100)->count();
        $inProgress = $events->filter(fn ($e) => $e->overallReadiness() > 0 && $e->overallReadiness() < 100)->count();
        $notStarted = $events->filter(fn ($e) => $e->overallReadiness() === 0)->count();
        $averageReadiness = $totalEvents > 0
            ? (int) round($events->avg(fn ($e) => $e->overallReadiness()))
            : 0;

        // Critical alerts
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

        $overdueCount = Requirement::whereIn('event_id', $events->pluck('id'))
            ->where('is_completed', false)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->when($user->isDepartmentScoped(), fn ($q) => $q->where('department_id', $user->department_id))
            ->count();

        // Director-specific enriched data
        if ($user->isDirector()) {
            return $this->directorView(
                $weeks, $selectedWeek, $events, $departments,
                $totalEvents, $fullyReady, $inProgress, $notStarted,
                $averageReadiness, $criticalAlerts, $overdueCount,
            );
        }

        return view('dashboard.index', compact(
            'weeks', 'selectedWeek', 'events', 'departments',
            'totalEvents', 'fullyReady', 'inProgress', 'notStarted', 'averageReadiness',
            'criticalAlerts', 'overdueCount',
        ));
    }

    private function directorView(
        $weeks, $selectedWeek, $events, $departments,
        int $totalEvents, int $fullyReady, int $inProgress, int $notStarted,
        int $averageReadiness, $criticalAlerts, int $overdueCount,
    ): View {
        // Department readiness across all events this week
        $deptReadiness = $departments->map(function (Department $dept) use ($events) {
            $allReqs = collect();
            foreach ($events as $event) {
                $allReqs = $allReqs->merge($event->requirements->where('department_id', $dept->id));
            }

            $total = $allReqs->count();
            $completed = $allReqs->where('is_completed', true)->count();
            $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            $criticalPending = $allReqs
                ->where('priority.value', Priority::Critical->value)
                ->where('is_completed', false)
                ->count();

            $overdue = $allReqs
                ->where('is_completed', false)
                ->filter(fn ($r) => $r->deadline && $r->deadline->isPast())
                ->count();

            // Per-event breakdown for the drill-down panel
            $eventBreakdown = $events->map(function ($event) use ($dept) {
                $reqs = $event->requirements->where('department_id', $dept->id);
                $total = $reqs->count();
                $completed = $reqs->where('is_completed', true)->count();
                $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

                $critical = $reqs->where('priority.value', Priority::Critical->value)->where('is_completed', false)->count();
                $overdue = $reqs->where('is_completed', false)->filter(fn ($r) => $r->deadline && $r->deadline->isPast())->count();

                return [
                    'event' => $event,
                    'total' => $total,
                    'completed' => $completed,
                    'pct' => $pct,
                    'critical' => $critical,
                    'overdue' => $overdue,
                    'classes' => Department::ragClasses($pct),
                ];
            })->filter(fn ($e) => $e['total'] > 0)->values();

            return [
                'department' => $dept,
                'total' => $total,
                'completed' => $completed,
                'percentage' => $pct,
                'criticalPending' => $criticalPending,
                'overdue' => $overdue,
                'classes' => Department::ragClasses($pct),
                'status' => Department::ragStatus($pct),
                'eventBreakdown' => $eventBreakdown,
            ];
        })->filter(fn ($d) => $d['total'] > 0)->sortBy('percentage')->values();

        // At-risk events
        $atRiskEvents = $events->filter(fn ($e) => $e->isAtRisk());

        // Total requirements stats
        $allEventIds = $events->pluck('id');
        $totalReqs = Requirement::whereIn('event_id', $allEventIds)->count();
        $completedReqs = Requirement::whereIn('event_id', $allEventIds)->where('is_completed', true)->count();
        $completionRate = $totalReqs > 0 ? (int) round(($completedReqs / $totalReqs) * 100) : 0;

        $criticalTotal = Requirement::whereIn('event_id', $allEventIds)->where('priority', Priority::Critical->value)->count();
        $criticalDone = Requirement::whereIn('event_id', $allEventIds)->where('priority', Priority::Critical->value)->where('is_completed', true)->count();

        // Serialisable payload for the Alpine dept drill-down panel
        $deptPanelData = $deptReadiness->map(function ($dr) {
            return [
                'name' => $dr['department']->name,
                'color' => $dr['department']->color,
                'breakdown' => $dr['eventBreakdown']->map(function ($eb) {
                    return [
                        'event_id' => $eb['event']->id,
                        'event_name' => $eb['event']->name,
                        'event_type' => $eb['event']->type,
                        'venue' => $eb['event']->venue ?? '',
                        'day' => $eb['event']->event_date->format('d'),
                        'month' => $eb['event']->event_date->format('M'),
                        'pct' => $eb['pct'],
                        'total' => $eb['total'],
                        'completed' => $eb['completed'],
                        'critical' => $eb['critical'],
                        'overdue' => $eb['overdue'],
                        'event_url' => route('events.show', $eb['event']),
                    ];
                })->values()->all(),
            ];
        })->values();

        return view('dashboard.director', compact(
            'weeks', 'selectedWeek', 'events', 'departments', 'deptReadiness', 'deptPanelData',
            'totalEvents', 'fullyReady', 'inProgress', 'notStarted',
            'averageReadiness', 'criticalAlerts', 'overdueCount',
            'atRiskEvents', 'totalReqs', 'completedReqs', 'completionRate',
            'criticalTotal', 'criticalDone',
        ));
    }
}
