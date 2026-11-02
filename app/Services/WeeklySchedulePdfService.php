<?php

namespace App\Services;

use App\Models\PlanningWeek;
use Barryvdh\DomPDF\Facade\Pdf;

class WeeklySchedulePdfService
{
    public function generate(PlanningWeek $planningWeek): string
    {
        $planningWeek->load('events');

        $grouped = $planningWeek->events->groupBy(fn ($e) => $e->schedule_category?->label() ?? 'Uncategorised');

        $stats = [
            'online' => 0,
            'non_residents' => $planningWeek->events->sum('participants_non_residential'),
            'residents' => $planningWeek->events->sum('participants_residential'),
            'offsite' => 0,
            'ksg_activities' => 0,
            'consultancies' => 0,
        ];

        $pdf = Pdf::loadView('reports.weekly-schedule-pdf', [
            'week' => $planningWeek,
            'grouped' => $grouped,
            'stats' => $stats,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = storage_path('app/temp/schedule-'.$planningWeek->id.'-'.time().'.pdf');

        if (! file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $pdf->save($filename);

        return $filename;
    }
}
