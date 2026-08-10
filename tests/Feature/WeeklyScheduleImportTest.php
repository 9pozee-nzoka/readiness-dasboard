<?php

use App\Enums\ScheduleCategory;
use App\Models\Event;
use App\Models\PlanningWeek;
use App\Models\User;
use App\Services\WeeklyScheduleImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports a weekly schedule json file into a planning week and events', function () {
    $result = app(WeeklyScheduleImportService::class)->importFromJsonFile(
        database_path('data/weekly-schedule-2026-05-18.json'),
    );

    expect($result['events_created'])->toBe(24)
        ->and($result['events_skipped'])->toBe(0)
        ->and($result['week'])->toBeInstanceOf(PlanningWeek::class);

    $week = PlanningWeek::query()->first();

    expect($week->week_start->format('Y-m-d'))->toBe('2026-05-18')
        ->and($week->week_end->format('Y-m-d'))->toBe('2026-05-22')
        ->and($week->campus)->toBe('Nairobi')
        ->and(Event::query()->count())->toBe(24);

    $customerService = Event::query()->where('name', 'Customer Service Training')->first();

    expect($customerService)->not->toBeNull()
        ->and($customerService->schedule_category)->toBe(ScheduleCategory::NewProgramme)
        ->and($customerService->client)->toBe('KSG')
        ->and($customerService->participants_non_residential)->toBe(25);
});

it('skips duplicate events on second import', function () {
    $service = app(WeeklyScheduleImportService::class);
    $service->importFromJsonFile(database_path('data/weekly-schedule-2026-05-18.json'));

    $second = $service->importFromJsonFile(database_path('data/weekly-schedule-2026-05-18.json'));

    expect($second['events_created'])->toBe(0)
        ->and($second['events_skipped'])->toBe(24)
        ->and(Event::query()->count())->toBe(24);
});

it('allows admins to import sample schedule from the ui', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.planning-weeks.import.store'), [
            'sample_schedule' => '2026-05-18',
        ])
        ->assertRedirect();

    expect(PlanningWeek::query()->count())->toBe(1)
        ->and(Event::query()->count())->toBe(24);
});
