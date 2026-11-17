<?php

namespace App\Console\Commands;

use App\Models\PlanningWeek;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('planning:update-current-week')]
#[Description('Automatically detect and update the current planning week based on today\'s date')]
class UpdateCurrentWeek extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Detecting current planning week...');

        $previousCurrent = PlanningWeek::where('is_current', true)->first();

        PlanningWeek::updateCurrentWeek();

        $newCurrent = PlanningWeek::where('is_current', true)->first();

        if (! $newCurrent) {
            $this->warn('No planning week found for today\'s date.');

            return self::FAILURE;
        }

        if ($previousCurrent && $previousCurrent->id !== $newCurrent->id) {
            $this->info("Current week changed from \"{$previousCurrent->label}\" to \"{$newCurrent->displayLabel()}\"");
        } else {
            $this->info("Current week: {$newCurrent->displayLabel()}");
        }

        return self::SUCCESS;
    }
}
