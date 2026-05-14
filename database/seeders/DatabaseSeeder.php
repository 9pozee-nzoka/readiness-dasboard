<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Event;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Departments ───────────────────────────────────────────
        $departments = collect([
            ['name' => 'ICT',             'color' => '#6366f1'],
            ['name' => 'Finance',         'color' => '#0ea5e9'],
            ['name' => 'Administration',  'color' => '#8b5cf6'],
            ['name' => 'Security',        'color' => '#ef4444'],
            ['name' => 'Logistics',       'color' => '#f97316'],
            ['name' => 'Communications',  'color' => '#14b8a6'],
            ['name' => 'Facilities',      'color' => '#84cc16'],
            ['name' => 'Human Resources', 'color' => '#ec4899'],
            ['name' => 'Protocol',        'color' => '#f59e0b'],
            ['name' => 'Catering',        'color' => '#10b981'],
        ])->map(fn ($d) => Department::create([
            'name'         => $d['name'],
            'slug'         => Str::slug($d['name']),
            'head_name'    => fake()->name(),
            'head_contact' => fake()->phoneNumber(),
            'color'        => $d['color'],
            'is_active'    => true,
        ]));

        // ── Users ─────────────────────────────────────────────────
        $demoPassword = env('DEMO_PASSWORD', 'demo123');

        // Admin
        User::create([
            'name'          => 'System Admin',
            'email'         => env('DEMO_ADMIN_EMAIL', 'admin@example.com'),
            'password'      => Hash::make($demoPassword),
            'role'          => UserRole::Admin,
            'department_id' => null,
            'is_approved'   => true,
            'approved_at'   => now(),
        ]);

        // Director
        User::create([
            'name'          => 'The Director',
            'email'         => env('DEMO_DIRECTOR_EMAIL', 'director@example.com'),
            'password'      => Hash::make($demoPassword),
            'role'          => UserRole::Director,
            'department_id' => null,
            'is_approved'   => true,
            'approved_at'   => now(),
        ]);

        // One HOD and one employee per department
        foreach ($departments as $dept) {
            User::create([
                'name'           => 'HOD '.$dept->name,
                'email'          => 'hod.'.Str::slug($dept->name).'@example.com',
                'password'       => Hash::make($demoPassword),
                'role'           => UserRole::Hod,
                'department_id'  => $dept->id,
                'declared_level' => 'hod',
                'is_approved'    => true,
                'approved_at'    => now(),
            ]);

            User::create([
                'name'           => 'Staff '.$dept->name,
                'email'          => 'staff.'.Str::slug($dept->name).'@example.com',
                'password'       => Hash::make($demoPassword),
                'role'           => UserRole::Employee,
                'department_id'  => $dept->id,
                'declared_level' => 'employee',
                'is_approved'    => true,
                'approved_at'    => now(),
            ]);
        }

        // ── Requirement templates ─────────────────────────────────
        $requirementTemplates = [
            'ICT'             => ['Set up audio-visual equipment', 'Test internet connectivity', 'Configure presentation systems', 'Ensure backup power for equipment'],
            'Finance'         => ['Prepare event budget summary', 'Process vendor payments', 'Confirm petty cash float', 'Prepare financial report'],
            'Administration'  => ['Prepare agenda and briefing documents', 'Confirm attendance list', 'Send invitations and reminders', 'Prepare minutes template'],
            'Security'        => ['Brief security personnel on access control', 'Set up perimeter security', 'Prepare visitor log sheets', 'Coordinate with external security'],
            'Logistics'       => ['Coordinate transport and parking', 'Arrange equipment delivery', 'Confirm supplier deliveries', 'Set up loading bay access'],
            'Communications'  => ['Prepare press releases', 'Set up social media coverage', 'Arrange photography and videography', 'Prepare signage and directional boards'],
            'Facilities'      => ['Prepare venue layout and seating arrangement', 'Arrange flowers and decorations', 'Ensure venue cleanliness', 'Check lighting and HVAC'],
            'Human Resources' => ['Confirm staff duty roster', 'Brief event volunteers', 'Prepare name tags and welcome packs', 'Coordinate staff transport'],
            'Protocol'        => ['Confirm VIP protocol arrangements', 'Prepare VIP seating plan', 'Coordinate MC and programme flow', 'Arrange VIP parking'],
            'Catering'        => ['Arrange catering and refreshments', 'Confirm dietary requirements', 'Set up catering stations', 'Coordinate catering staff'],
        ];

        // ── Planning weeks ────────────────────────────────────────
        $weeks = [
            PlanningWeek::create(['label' => 'Week of 05 May 2026', 'week_start' => '2026-05-05', 'week_end' => '2026-05-09', 'is_current' => false]),
            PlanningWeek::create(['label' => 'Week of 12 May 2026', 'week_start' => '2026-05-12', 'week_end' => '2026-05-16', 'is_current' => true]),
            PlanningWeek::create(['label' => 'Week of 19 May 2026', 'week_start' => '2026-05-19', 'week_end' => '2026-05-23', 'is_current' => false]),
        ];

        $eventData = [
            [
                ['name' => 'Board Meeting Q2 2026',  'type' => 'Meeting',  'date' => '2026-05-06', 'time' => '09:00', 'venue' => 'Main Boardroom'],
                ['name' => 'Staff Training Day',     'type' => 'Training', 'date' => '2026-05-08', 'time' => '08:00', 'venue' => 'Training Room 1'],
            ],
            [
                ['name' => 'Annual General Meeting 2026', 'type' => 'Meeting', 'date' => '2026-05-13', 'time' => '10:00', 'venue' => 'Auditorium'],
                ['name' => 'Budget Review Forum',         'type' => 'Forum',   'date' => '2026-05-14', 'time' => '14:00', 'venue' => 'Conference Hall A'],
                ['name' => 'Leadership Retreat',          'type' => 'Retreat', 'date' => '2026-05-15', 'time' => '08:00', 'venue' => 'Executive Suite'],
            ],
            [
                ['name' => 'Graduation Ceremony 2026', 'type' => 'Ceremony', 'date' => '2026-05-20', 'time' => '09:00', 'venue' => 'Open Grounds'],
                ['name' => 'Stakeholder Forum',        'type' => 'Forum',    'date' => '2026-05-21', 'time' => '13:00', 'venue' => 'Conference Hall A'],
            ],
        ];

        foreach ($weeks as $weekIndex => $week) {
            foreach ($eventData[$weekIndex] as $ed) {
                $event = Event::create([
                    'planning_week_id' => $week->id,
                    'name'             => $ed['name'],
                    'type'             => $ed['type'],
                    'event_date'       => $ed['date'],
                    'event_time'       => $ed['time'],
                    'venue'            => $ed['venue'],
                    'description'      => 'Organised by the Administration department.',
                ]);

                foreach ($departments as $dept) {
                    $templates = $requirementTemplates[$dept->name] ?? ['Prepare department readiness report'];

                    foreach ($templates as $description) {
                        $isCompleted = match ($weekIndex) {
                            0 => true,
                            1 => fake()->boolean(55),
                            2 => fake()->boolean(15),
                        };

                        // Assign realistic priorities — first requirement per dept is critical, second high, rest mixed
                        $priorityOptions = [\App\Enums\Priority::Critical, \App\Enums\Priority::High, \App\Enums\Priority::Medium, \App\Enums\Priority::Low];
                        $priority = fake()->randomElement($priorityOptions);

                        // Deadline = 1-2 days before the event
                        $deadline = \Carbon\Carbon::parse($ed['date'])->subDays(fake()->numberBetween(1, 3))->toDateString();

                        Requirement::create([
                            'event_id'            => $event->id,
                            'department_id'       => $dept->id,
                            'description'         => $description,
                            'priority'            => $priority->value,
                            'deadline'            => $deadline,
                            'is_completed'        => $isCompleted,
                            'responsible_officer' => fake()->name(),
                            'completed_at'        => $isCompleted ? fake()->dateTimeBetween('-1 week', 'now') : null,
                            'is_escalated'        => false,
                        ]);
                    }
                }
            }
        }
    }
}
