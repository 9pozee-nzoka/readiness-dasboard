<?php

namespace Database\Seeders;

use App\Models\FinancialYear;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinancialYearSeeder extends Seeder
{
    /**
     * Seed financial years and auto-generate planning weeks.
     * KSG Financial Year runs July 1 - June 30.
     */
    public function run(): void
    {
        $this->command->info('Seeding financial years...');

        // Create financial years: 2024/2025, 2025/2026, 2026/2027
        $financialYears = [
            [
                'name' => '2024/2025',
                'start_date' => Carbon::create(2024, 7, 1),
                'end_date' => Carbon::create(2025, 6, 30),
                'is_current' => false,
            ],
            [
                'name' => '2025/2026',
                'start_date' => Carbon::create(2025, 7, 1),
                'end_date' => Carbon::create(2026, 6, 30),
                'is_current' => false,
            ],
            [
                'name' => '2026/2027',
                'start_date' => Carbon::create(2026, 7, 1),
                'end_date' => Carbon::create(2027, 6, 30),
                'is_current' => true, // Current FY
            ],
        ];

        foreach ($financialYears as $fyData) {
            $fy = FinancialYear::updateOrCreate(
                ['name' => $fyData['name']],
                $fyData
            );

            // Auto-generate planning weeks for Nairobi campus
            $created = $fy->generateWeeks('NRB', force: false);

            if ($created > 0) {
                $this->command->info("  → Generated {$created} weeks for {$fy->name} (NRB)");
            } else {
                $this->command->info("  → Weeks already exist for {$fy->name} (NRB)");
            }
        }

        $this->command->info('Financial year seeding complete!');
    }
}
