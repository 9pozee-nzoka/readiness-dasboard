<?php

use App\Console\Commands\EscalateOverdueRequirements;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-escalate overdue requirements every morning at 07:00
Schedule::command(EscalateOverdueRequirements::class)
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->runInBackground();
