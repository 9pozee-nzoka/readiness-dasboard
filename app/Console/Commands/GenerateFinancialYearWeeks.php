<?php

namespace App\Console\Commands;

use App\Models\FinancialYear;
use App\Models\PlanningWeek;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateFinancialYearWeeks extends Command
{
    protected $signature = 'fy:generate-weeks 
                            {financial_year_id : The ID of the financial year}
                            {--campus=NRB : Campus code (NRB, MTB, BRC, LWR)}
                            {--force : Overwrite existing weeks}';

    protected $description = 'Generate planning weeks for a financial year with auto-generated references';

    public function handle(): int
    {
        $fyId = $this->argument('financial_year_id');
        $campus = strtoupper($this->option('campus'));
        $force = $this->option('force');

        $fy = FinancialYear::find($fyId);

        if (! $fy) {
            $this->error("Financial year #{$fyId} not found.");

            return self::FAILURE;
        }

        // Validate campus code
        $validCampuses = ['NRB', 'MTB', 'BRC', 'LWR'];
        if (! in_array($campus, $validCampuses)) {
            $this->error('Invalid campus code. Valid options: '.implode(', ', $validCampuses));

            return self::FAILURE;
        }

        // Check if weeks already exist
        $existingCount = PlanningWeek::where('financial_year_id', $fy->id)
            ->where('campus', $campus)
            ->count();

        if ($existingCount > 0 && ! $force) {
            $this->error("Financial year {$fy->name} already has {$existingCount} weeks for campus {$campus}. Use --force to regenerate.");

            return self::FAILURE;
        }

        if ($force && $existingCount > 0) {
            PlanningWeek::where('financial_year_id', $fy->id)
                ->where('campus', $campus)
                ->delete();
            $this->warn("Deleted {$existingCount} existing weeks for {$campus}.");
        }

        $this->info("Generating planning weeks for {$fy->name} ({$campus} campus)...");

        // FY short code (e.g., "26-27" for 2026/2027)
        $fyCode = $this->getFyShortCode($fy->name);

        // Start from the first Monday of the FY
        $current = $fy->start_date->copy()->startOfWeek(Carbon::MONDAY);

        // If FY starts mid-week, adjust to the first full Monday-Sunday week
        if ($current->lt($fy->start_date)) {
            $current->addWeek();
        }

        $weekNumber = 1;
        $created = 0;

        while ($current->lte($fy->end_date)) {
            $weekStart = $current->copy();
            $weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY);

            // Don't exceed FY end date
            if ($weekEnd->gt($fy->end_date)) {
                break;
            }

            // Generate reference: KSG/NRB/26-27/Week-001
            $reference = sprintf('KSG/%s/%s/Week-%03d', $campus, $fyCode, $weekNumber);

            // Generate label: 18-24 May 2026
            $label = $this->generateLabel($weekStart, $weekEnd);

            PlanningWeek::create([
                'label' => $label,
                'financial_year' => $fy->name,
                'week_number' => $weekNumber,
                'financial_year_id' => $fy->id,
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'is_current' => false,
                'is_transition_week' => false,
                'campus' => $campus,
                'schedule_reference' => $reference,
            ]);

            $created++;
            $weekNumber++;
            $current->addWeek();
        }

        $this->info("✓ Created {$created} planning weeks for {$fy->name} ({$campus}).");

        return self::SUCCESS;
    }

    /**
     * Extract FY short code from name (e.g., "2026/2027" → "26-27").
     */
    private function getFyShortCode(string $name): string
    {
        // Match patterns like "2026/2027" or "2026-2027"
        if (preg_match('/(\d{2})(\d{2})[\/\-](\d{2})(\d{2})/', $name, $matches)) {
            return "{$matches[2]}-{$matches[4]}";
        }

        // Fallback: use last 2 digits of each year
        $parts = preg_split('/[\/\-]/', $name);
        if (count($parts) === 2) {
            return substr($parts[0], -2).'-'.substr($parts[1], -2);
        }

        return 'XX-XX';
    }

    /**
     * Generate week label: "18-24 May 2026" or "29 Jun - 05 Jul 2026".
     */
    private function generateLabel(Carbon $start, Carbon $end): string
    {
        if ($start->month === $end->month) {
            // Same month: "18-24 May 2026"
            return sprintf(
                '%d-%d %s %d',
                $start->day,
                $end->day,
                $start->format('M'),
                $start->year
            );
        } else {
            // Crosses months: "29 Jun - 05 Jul 2026"
            return sprintf(
                '%d %s - %d %s %d',
                $start->day,
                $start->format('M'),
                $end->day,
                $end->format('M'),
                $end->year
            );
        }
    }
}
