<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Event;
use App\Models\PlanningWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Printable HTML readiness report for a single event.
     * Open in browser → Print → Save as PDF.
     */
    public function eventReport(Event $event): View
    {
        $user = Auth::user();
        $event->load(['planningWeek', 'requirements.department', 'risks']);

        $departments = Department::where('is_active', true)
            ->when($user->isDepartmentScoped(), fn ($q) => $q->where('id', $user->department_id))
            ->orderBy('name')
            ->get();

        $departmentReadiness = $departments->map(function ($dept) use ($event) {
            $reqs = $event->requirements->where('department_id', $dept->id)->sortBy(fn ($r) => $r->priority->sortOrder())->values();
            $total = $reqs->count();
            $completed = $reqs->where('is_completed', true)->count();
            $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            return compact('dept', 'reqs', 'total', 'completed', 'pct');
        })->filter(fn ($d) => $d['total'] > 0)->values();

        $overall = $event->overallReadiness();
        $weighted = $event->weightedReadiness();
        $riskScore = $event->riskScore();
        $criticalPending = $event->criticalPendingCount();

        return view('reports.event', compact(
            'event', 'departmentReadiness', 'overall', 'weighted',
            'riskScore', 'criticalPending'
        ));
    }

    /**
     * Weekly summary CSV — all events in a planning week.
     */
    public function weeklyCsv(Request $request): StreamedResponse
    {
        $weekId = $request->integer('week') ?: PlanningWeek::where('is_current', true)->value('id');
        $week = PlanningWeek::findOrFail($weekId);

        $events = Event::with(['requirements.department'])
            ->where('planning_week_id', $weekId)
            ->orderBy('event_date')
            ->get();

        $filename = 'readiness-report-'.str($week->label)->slug().'.csv';

        return response()->streamDownload(function () use ($events, $week) {
            $h = fopen('php://output', 'w');

            fputcsv($h, ['Weekly Readiness Report — '.$week->label]);
            fputcsv($h, ['Generated', now()->format('d M Y H:i')]);
            fputcsv($h, []);
            fputcsv($h, ['Event', 'Type', 'Date', 'Venue', 'Overall %', 'Weighted %', 'Risk Score', 'Department', 'Requirement', 'Priority', 'Status', 'Deadline', 'Officer']);

            foreach ($events as $event) {
                foreach ($event->requirements->sortBy('department.name') as $req) {
                    fputcsv($h, [
                        $event->name,
                        $event->type,
                        $event->event_date->format('d M Y'),
                        $event->venue ?? '—',
                        $event->overallReadiness().'%',
                        $event->weightedReadiness().'%',
                        $event->riskScore(),
                        $req->department->name,
                        $req->description,
                        $req->priority->label(),
                        $req->is_completed ? 'Completed' : 'Pending',
                        $req->deadline?->format('d M Y') ?? '—',
                        $req->responsible_officer ?? '—',
                    ]);
                }
            }

            fclose($h);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
