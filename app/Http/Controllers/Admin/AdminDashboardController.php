<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliberationStatus;
use App\Http\Controllers\Controller;
use App\Models\Deliberation;
use App\Models\Department;
use App\Models\Event;
use App\Models\PageVisit;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $totalUsers = User::where('is_approved', true)->count();
        $pendingApprovals = User::where('is_approved', false)->count();
        $totalEvents = Event::count();
        $totalDepartments = Department::where('is_active', true)->count();

        // Current week events
        $currentWeek = PlanningWeek::where('is_current', true)->first();
        $currentEvents = $currentWeek
            ? Event::with('requirements')->where('planning_week_id', $currentWeek->id)->get()
            : collect();

        $avgReadiness = $currentEvents->count() > 0
            ? (int) round($currentEvents->avg(fn ($e) => $e->overallReadiness()))
            : 0;

        $avgDecisionReadiness = $currentEvents->count() > 0
            ? (int) round($currentEvents->avg(fn ($e) => $e->decisionReadiness()))
            : 0;

        // Upcoming events (next 14 days)
        $upcomingEvents = Event::with('planningWeek')
            ->where('event_date', '>=', now()->toDateString())
            ->where('event_date', '<=', now()->addDays(14)->toDateString())
            ->orderBy('event_date')
            ->limit(5)
            ->get();

        // Visitor stats — last 7 days
        $visitorsToday = PageVisit::whereDate('visited_at', today())->count();
        $visitorsThisWeek = PageVisit::where('visited_at', '>=', now()->startOfWeek())->count();

        // Requirements completion rate
        $totalReqs = Requirement::count();
        $completedReqs = Requirement::where('status', 'completed')->count();
        $completionRate = $totalReqs > 0 ? (int) round(($completedReqs / $totalReqs) * 100) : 0;

        // Pending deliberations
        $pendingDeliberations = Deliberation::where('status', DeliberationStatus::Open)
            ->with(['event', 'requirement'])
            ->orderByRaw("CASE impact_level WHEN 'critical' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('decision_due_date')
            ->limit(5)
            ->get();

        $overdueDeliberations = Deliberation::where('status', DeliberationStatus::Open)
            ->whereNotNull('decision_due_date')
            ->where('decision_due_date', '<', now()->toDateString())
            ->count();

        // Department readiness for current week
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $deptReadiness = $departments->map(function ($dept) use ($currentEvents) {
            $total = 0;
            $completed = 0;
            foreach ($currentEvents as $event) {
                $reqs = $event->requirements->where('department_id', $dept->id);
                $total += $reqs->count();
                $completed += $reqs->sum(fn ($r) => $r->status->readinessWeight());
            }
            $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            return ['department' => $dept, 'percentage' => $pct, 'total' => $total, 'completed' => $completed];
        })->filter(fn ($d) => $d['total'] > 0)->sortBy('percentage')->values();

        // Visits per day for the last 7 days (chart data)
        $visitsByDay = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'date' => now()->subDays($daysAgo)->format('D'),
                'count' => PageVisit::whereDate('visited_at', $date)->count(),
            ];
        });

        return view('admin.dashboard', compact(
            'totalUsers', 'pendingApprovals', 'totalEvents', 'totalDepartments',
            'avgReadiness', 'avgDecisionReadiness', 'upcomingEvents', 'visitorsToday', 'visitorsThisWeek',
            'completionRate', 'deptReadiness', 'visitsByDay',
            'pendingDeliberations', 'overdueDeliberations',
        ));
    }
}
