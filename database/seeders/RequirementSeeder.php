<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\RequirementStatus;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventTypeRequirement;
use App\Models\Requirement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RequirementSeeder extends Seeder
{
    /**
     * Fallback event type for events whose type has no template requirements.
     */
    private const string FALLBACK_EVENT_TYPE = 'Meeting';

    /**
     * Weighted distribution of requirement statuses for seeded sample data.
     *
     * @var array<string, int>
     */
    private const array STATUS_WEIGHTS = [
        'not_started' => 15,
        'in_progress' => 20,
        'under_deliberation' => 10,
        'awaiting_approval' => 8,
        'approved' => 7,
        'procurement_ongoing' => 5,
        'blocked' => 3,
        'escalated' => 2,
        'completed' => 30,
    ];

    /**
     * Generic sample requirements used when no templates are available.
     *
     * @var list<array{description: string, priority: string}>
     */
    private const array GENERIC_REQUIREMENTS = [
        ['description' => 'Prepare backup power plan', 'priority' => 'high'],
        ['description' => 'Confirm backup venue option', 'priority' => 'medium'],
        ['description' => 'Arrange transport for VIP guests', 'priority' => 'medium'],
        ['description' => 'Prepare emergency contact list', 'priority' => 'high'],
        ['description' => 'Test backup internet connection', 'priority' => 'medium'],
        ['description' => 'Print additional name tags', 'priority' => 'low'],
        ['description' => 'Prepare welcome speech draft', 'priority' => 'medium'],
        ['description' => 'Confirm MC availability', 'priority' => 'medium'],
        ['description' => 'Arrange press conference room', 'priority' => 'low'],
        ['description' => 'Prepare feedback survey forms', 'priority' => 'low'],
    ];

    public function run(): void
    {
        // Skip in production - this seeder creates demo requirements using fake()
        if (app()->environment('production')) {
            $this->command->warn('Skipping requirement seeder in production environment.');

            return;
        }

        $events = Event::doesntHave('requirements')->get();

        if ($events->isEmpty()) {
            $this->command->info('All seeded events already have requirements.');

            return;
        }

        $this->command->info("Seeding sample requirements for {$events->count()} events...");

        $departments = Department::where('is_active', true)->get();
        $templates = EventTypeRequirement::with('department')->get();
        $hodUsers = User::where('role', 'hod')->where('is_approved', true)->get()->keyBy('department_id');
        $employees = User::where('role', 'employee')->where('is_approved', true)->get()->keyBy('department_id');

        $createdCount = 0;

        foreach ($events as $event) {
            $typeTemplates = $templates->where('event_type', $event->type);

            if ($typeTemplates->isEmpty()) {
                $typeTemplates = $templates->where('event_type', self::FALLBACK_EVENT_TYPE);
            }

            foreach ($typeTemplates as $template) {
                $this->createRequirement(
                    $event,
                    $template->department_id,
                    $template->description,
                    $template->priority,
                    $hodUsers,
                    $employees,
                );
                $createdCount++;
            }

            $customCount = fake()->numberBetween(2, 5);
            for ($i = 0; $i < $customCount; $i++) {
                $generic = fake()->randomElement(self::GENERIC_REQUIREMENTS);
                $department = $departments->random();

                $this->createRequirement(
                    $event,
                    $department->id,
                    $generic['description'],
                    Priority::from($generic['priority']),
                    $hodUsers,
                    $employees,
                );
                $createdCount++;
            }
        }

        $this->command->info("Created {$createdCount} sample requirements successfully.");
    }

    /**
     * Create a single sample requirement for the given event.
     */
    private function createRequirement(
        Event $event,
        int $departmentId,
        string $description,
        Priority $priority,
        $hodUsers,
        $employees,
    ): void {
        $statusValue = fake()->randomElement(array_keys(self::STATUS_WEIGHTS));
        $status = RequirementStatus::from($statusValue);

        $deadline = fake()->dateTimeBetween(
            $event->event_date->copy()->subDays(7),
            $event->event_date,
        )->format('Y-m-d');

        $completedAt = null;
        if ($status->isCompleted()) {
            $deadlineDate = Carbon::parse($deadline);
            $start = $deadlineDate->copy()->subDays(2);
            $end = now();
            if ($start->greaterThan($end)) {
                $start = $end->copy()->subDay();
            }
            $completedAt = fake()->dateTimeBetween($start, $end);
        }

        $hod = $hodUsers->get($departmentId);
        $employee = $employees->get($departmentId);

        Requirement::create([
            'event_id' => $event->id,
            'department_id' => $departmentId,
            'description' => $description,
            'priority' => $priority->value,
            'status' => $status->value,
            'deadline' => $deadline,
            'is_completed' => $status->isCompleted(),
            'responsible_officer' => $hod?->name ?? fake()->name(),
            'assigned_to' => $employee?->id,
            'requires_deliberation' => in_array($statusValue, ['under_deliberation', 'awaiting_approval', 'blocked']),
            'completed_at' => $completedAt,
            'is_escalated' => $status->isEscalated(),
            'escalated_at' => $status->isEscalated() ? now()->subDays(fake()->numberBetween(1, 3)) : null,
        ]);
    }
}
