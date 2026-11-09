<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\RequirementStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventTypeRequirement;
use App\Models\PageVisit;
use App\Models\PlanningWeek;
use App\Models\Requirement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skip demo data in production
        if (app()->environment('production')) {
            $this->command->warn('Skipping demo data seeder in production environment.');

            return;
        }

        $this->command->info('Seeding demo data...');

        // ── 1. Planning Weeks ───────────────────────────────────────
        $this->command->info('  → Planning weeks');
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $currentWeekStart = Carbon::now()->startOfWeek();
        $nextWeek1Start = Carbon::now()->addWeek()->startOfWeek();
        $nextWeek2Start = Carbon::now()->addWeeks(2)->startOfWeek();

        // Helper to calculate financial year and week number
        // KSG financial year runs July 1 - June 30
        $calculateFYWeek = function (Carbon $date) {
            $fyStartMonth = 7; // July
            $year = $date->year;

            // If we're before July, we're in FY that started last year
            if ($date->month < $fyStartMonth) {
                $fyStart = Carbon::create($year - 1, $fyStartMonth, 1);
                $fy = ($year - 1).'/'.$year;
            } else {
                $fyStart = Carbon::create($year, $fyStartMonth, 1);
                $fy = $year.'/'.($year + 1);
            }

            // Calculate week number (1-52)
            $weekNumber = (int) ceil($date->diffInDays($fyStart->startOfWeek()) / 7) + 1;
            $weekNumber = min(max($weekNumber, 1), 52); // Clamp between 1-52

            return ['financial_year' => $fy, 'week_number' => $weekNumber];
        };

        $weeks = [
            array_merge(['label' => 'Week of '.$lastWeekStart->format('d M Y'), 'week_start' => $lastWeekStart->copy(), 'week_end' => $lastWeekStart->copy()->addDays(4), 'is_current' => false], $calculateFYWeek($lastWeekStart)),
            array_merge(['label' => 'Week of '.$currentWeekStart->format('d M Y'), 'week_start' => $currentWeekStart->copy(), 'week_end' => $currentWeekStart->copy()->addDays(4), 'is_current' => true], $calculateFYWeek($currentWeekStart)),
            array_merge(['label' => 'Week of '.$nextWeek1Start->format('d M Y'), 'week_start' => $nextWeek1Start->copy(), 'week_end' => $nextWeek1Start->copy()->addDays(4), 'is_current' => false], $calculateFYWeek($nextWeek1Start)),
            array_merge(['label' => 'Week of '.$nextWeek2Start->format('d M Y'), 'week_start' => $nextWeek2Start->copy(), 'week_end' => $nextWeek2Start->copy()->addDays(4), 'is_current' => false], $calculateFYWeek($nextWeek2Start)),
        ];

        $planningWeeks = collect();
        foreach ($weeks as $w) {
            $planningWeeks->push(PlanningWeek::create($w));
        }

        // ── 2. Staff Users ────────────────────────────────────────────
        $this->command->info('  → Staff users');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $director = User::updateOrCreate(
            ['email' => 'director@ksg.ac.ke'],
            [
                'name' => 'Dr. Margaret Wanjiku',
                'password' => 'Pozee@5268',
                'role' => UserRole::Director,
                'department_id' => null,
                'is_approved' => true,
                'approved_at' => now(),
            ]
        );

        $hodUsers = collect();
        $hodNames = [
            'ICT' => 'James Mwangi',
            'Finance' => 'Grace Akinyi',
            'Administration' => 'Peter Ochieng',
            'Security' => 'John Kamau',
            'Logistics' => 'Mary Njeri',
            'Communications' => 'Lucy Wambui',
            'Facilities' => 'Robert Otieno',
            'Human Resources' => 'Ann Chebet',
            'Protocol' => 'Catherine Muthoni',
            'Catering' => 'David Kipchirchir',
        ];

        foreach ($departments as $dept) {
            $hodUsers->push(User::updateOrCreate(
                ['email' => strtolower(str_replace(' ', '-', $dept->name)).'-hod@ksg.ac.ke'],
                [
                    'name' => $hodNames[$dept->name] ?? fake()->name(),
                    'password' => 'Pozee@5268',
                    'role' => UserRole::Hod,
                    'department_id' => $dept->id,
                    'is_approved' => true,
                    'approved_at' => now(),
                ]
            ));
        }

        $employees = collect([
            ['name' => 'Kevin Omondi', 'email' => 'kevin.omondi@ksg.ac.ke', 'dept' => 'ICT'],
            ['name' => 'Sarah Achieng', 'email' => 'sarah.achieng@ksg.ac.ke', 'dept' => 'Communications'],
            ['name' => 'Paul Kiptoo', 'email' => 'paul.kiptoo@ksg.ac.ke', 'dept' => 'Facilities'],
        ]);

        foreach ($employees as $emp) {
            $dept = $departments->firstWhere('name', $emp['dept']);
            User::updateOrCreate(
                ['email' => $emp['email']],
                [
                    'name' => $emp['name'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Employee,
                    'department_id' => $dept?->id,
                    'is_approved' => true,
                    'approved_at' => now(),
                ]
            );
        }

        // Update department head names
        foreach ($departments as $dept) {
            $hod = $hodUsers->firstWhere('department_id', $dept->id);
            if ($hod) {
                $dept->update(['head_name' => $hod->name]);
            }
        }

        // ── 3. Events ─────────────────────────────────────────────────
        $this->command->info('  → Events');
        $eventData = [
            // Last week (past events — some completed, some still tracked)
            [
                'week_index' => 0,
                'name' => 'Senior Management Retreat 2026',
                'type' => 'Retreat',
                'schedule_category' => 'ksg_activity',
                'event_date' => $lastWeekStart->copy()->addDays(2),
                'event_date_end' => $lastWeekStart->copy()->addDays(4),
                'event_time' => '08:00',
                'venue' => 'KSG Mombasa Campus',
                'coordinator' => 'Dr. Margaret Wanjiku',
                'programme_assistant' => 'Grace Akinyi',
                'participants_residential' => 45,
                'participants_non_residential' => 5,
                'description' => 'Annual senior management strategic planning retreat.',
            ],
            [
                'week_index' => 0,
                'name' => 'Board of Directors Meeting',
                'type' => 'Meeting',
                'schedule_category' => 'ksg_activity',
                'event_date' => $lastWeekStart->copy()->addDays(1),
                'event_time' => '10:00',
                'venue' => 'Main Boardroom',
                'coordinator' => 'Peter Ochieng',
                'participants_non_residential' => 12,
                'description' => 'Quarterly board review of institutional performance.',
            ],
            // Current week
            [
                'week_index' => 1,
                'name' => 'Leadership Training Programme — Cohort 4',
                'type' => 'Training',
                'schedule_category' => 'new_programme',
                'event_date' => $currentWeekStart->copy()->addDays(1),
                'event_date_end' => $currentWeekStart->copy()->addDays(3),
                'event_time' => '08:30',
                'venue' => 'Conference Hall A',
                'coordinator' => 'Dr. Margaret Wanjiku',
                'programme_assistant' => 'Ann Chebet',
                'participants_residential' => 80,
                'participants_non_residential' => 20,
                'client' => 'Ministry of Interior',
                'description' => 'Senior leadership training for principal secretaries and directors.',
            ],
            [
                'week_index' => 1,
                'name' => 'Budget Review Forum',
                'type' => 'Forum',
                'schedule_category' => 'ksg_activity',
                'event_date' => $currentWeekStart->copy()->addDays(2),
                'event_time' => '14:00',
                'venue' => 'Executive Suite',
                'coordinator' => 'Grace Akinyi',
                'participants_non_residential' => 25,
                'description' => 'FY2026/27 budget review and resource allocation discussion.',
            ],
            [
                'week_index' => 1,
                'name' => 'ICT Infrastructure Audit',
                'type' => 'Review',
                'schedule_category' => 'consultancy',
                'event_date' => $currentWeekStart->copy()->addDays(3),
                'event_time' => '09:00',
                'venue' => 'ICT Lab',
                'coordinator' => 'James Mwangi',
                'participants_non_residential' => 8,
                'description' => 'Comprehensive audit of campus ICT infrastructure and systems.',
            ],
            [
                'week_index' => 1,
                'name' => 'Staff Welfare Committee Meeting',
                'type' => 'Meeting',
                'schedule_category' => 'ksg_activity',
                'event_date' => $currentWeekStart->copy()->addDays(4),
                'event_time' => '11:00',
                'venue' => 'Training Room 1',
                'coordinator' => 'Ann Chebet',
                'participants_non_residential' => 10,
                'description' => 'Monthly staff welfare and grievances committee meeting.',
            ],
            // Next week
            [
                'week_index' => 2,
                'name' => 'Graduation Ceremony — Class of 2026',
                'type' => 'Ceremony',
                'schedule_category' => 'hosted',
                'event_date' => $nextWeek1Start->copy()->addDays(3),
                'event_time' => '09:00',
                'venue' => 'Open Grounds',
                'coordinator' => 'Catherine Muthoni',
                'programme_assistant' => 'Mary Njeri',
                'participants_residential' => 120,
                'participants_non_residential' => 300,
                'description' => 'Annual graduation ceremony for senior leadership programme participants.',
            ],
            [
                'week_index' => 2,
                'name' => 'Devolution Policy Workshop',
                'type' => 'Workshop',
                'schedule_category' => 'new_programme',
                'event_date' => $nextWeek1Start->copy()->addDays(1),
                'event_date_end' => $nextWeek1Start->copy()->addDays(2),
                'event_time' => '08:00',
                'venue' => 'Auditorium',
                'coordinator' => 'Dr. Margaret Wanjiku',
                'programme_assistant' => 'Lucy Wambui',
                'participants_residential' => 60,
                'participants_non_residential' => 40,
                'client' => 'Council of Governors',
                'description' => 'Multi-stakeholder workshop on devolution policy implementation.',
            ],
            [
                'week_index' => 2,
                'name' => 'Security Protocol Briefing',
                'type' => 'Meeting',
                'schedule_category' => 'ksg_activity',
                'event_date' => $nextWeek1Start->copy()->addDays(0),
                'event_time' => '07:30',
                'venue' => 'Security Briefing Room',
                'coordinator' => 'John Kamau',
                'participants_non_residential' => 15,
                'description' => 'Pre-event security briefing for all upcoming high-profile events.',
            ],
            // Week after next
            [
                'week_index' => 3,
                'name' => 'Public Service Transformation Forum',
                'type' => 'Forum',
                'schedule_category' => 'ongoing',
                'event_date' => $nextWeek2Start->copy()->addDays(2),
                'event_date_end' => $nextWeek2Start->copy()->addDays(3),
                'event_time' => '08:30',
                'venue' => 'Conference Hall A',
                'coordinator' => 'Dr. Margaret Wanjiku',
                'programme_assistant' => 'Peter Ochieng',
                'participants_residential' => 55,
                'participants_non_residential' => 45,
                'client' => 'Public Service Commission',
                'description' => 'National forum on public service transformation and digitization.',
            ],
            [
                'week_index' => 3,
                'name' => 'Facilities Maintenance Review',
                'type' => 'Review',
                'schedule_category' => 'consultancy',
                'event_date' => $nextWeek2Start->copy()->addDays(1),
                'event_time' => '10:00',
                'venue' => 'Facilities Office',
                'coordinator' => 'Robert Otieno',
                'participants_non_residential' => 6,
                'description' => 'Quarterly review of campus facilities maintenance schedules.',
            ],
        ];

        $events = collect();
        foreach ($eventData as $data) {
            $week = $planningWeeks[$data['week_index']];
            $events->push(Event::create([
                'planning_week_id' => $week->id,
                'name' => $data['name'],
                'type' => $data['type'],
                'schedule_category' => $data['schedule_category'] ?? null,
                'event_date' => $data['event_date']->format('Y-m-d'),
                'event_date_end' => isset($data['event_date_end']) ? $data['event_date_end']->format('Y-m-d') : null,
                'event_time' => $data['event_time'] ?? null,
                'venue' => $data['venue'] ?? null,
                'coordinator' => $data['coordinator'] ?? null,
                'programme_assistant' => $data['programme_assistant'] ?? null,
                'participants_residential' => $data['participants_residential'] ?? null,
                'participants_non_residential' => $data['participants_non_residential'] ?? null,
                'client' => $data['client'] ?? null,
                'description' => $data['description'] ?? null,
            ]));
        }

        // ── 4. Requirements from Templates ────────────────────────────
        $this->command->info('  → Requirements');
        $templates = EventTypeRequirement::with('department')->get();

        $statuses = [
            'not_started' => 30,
            'in_progress' => 40,
            'completed' => 30,
        ];

        $requirementsToInsert = [];

        foreach ($events as $event) {
            $typeTemplates = $templates->where('event_type', $event->type)->take(8); // Limit to 8 templates per event

            foreach ($typeTemplates as $template) {
                $statusValue = fake()->randomElement(array_keys($statuses));
                $status = RequirementStatus::from($statusValue);
                $deadline = fake()->dateTimeBetween($event->event_date->copy()->subDays(5), $event->event_date)->format('Y-m-d');
                $completedAt = null;
                if ($status->isCompleted()) {
                    $deadlineDate = Carbon::parse($deadline);
                    $start = $deadlineDate->copy()->subDays(2);
                    $end = now();
                    if ($start->greaterThan($end)) {
                        $start = $end->copy()->subDay();
                    }
                    $completedAt = fake()->dateTimeBetween($start, $end)->format('Y-m-d H:i:s');
                }

                $hodName = $hodNames[$template->department->name] ?? fake()->name();
                $employee = User::where('department_id', $template->department_id)
                    ->where('role', 'employee')
                    ->first();

                $requirementsToInsert[] = [
                    'event_id' => $event->id,
                    'department_id' => $template->department_id,
                    'description' => $template->description,
                    'priority' => $template->priority->value,
                    'status' => $status->value,
                    'deadline' => $deadline,
                    'is_completed' => $status->isCompleted(),
                    'responsible_officer' => $hodName,
                    'assigned_to' => $employee?->id,
                    'completed_at' => $completedAt,
                    'is_escalated' => $status->isEscalated(),
                    'escalated_at' => $status->isEscalated() ? now()->subDays(fake()->numberBetween(1, 3))->format('Y-m-d H:i:s') : null,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ];
            }

            // Add 2 custom requirements per event for variety
            for ($i = 0; $i < 2; $i++) {
                $dept = $departments->random();
                $priority = fake()->randomElement(Priority::cases());
                $statusValue = fake()->randomElement(array_keys($statuses));
                $status = RequirementStatus::from($statusValue);
                $deadline = fake()->dateTimeBetween($event->event_date->copy()->subDays(7), $event->event_date)->format('Y-m-d');
                $completedAt = null;
                if ($status->isCompleted()) {
                    $deadlineDate = Carbon::parse($deadline);
                    $start = $deadlineDate->copy()->subDays(2);
                    $end = now();
                    if ($start->greaterThan($end)) {
                        $start = $end->copy()->subDay();
                    }
                    $completedAt = fake()->dateTimeBetween($start, $end)->format('Y-m-d H:i:s');
                }

                $requirementsToInsert[] = [
                    'event_id' => $event->id,
                    'department_id' => $dept->id,
                    'description' => fake()->randomElement([
                        'Prepare backup power plan',
                        'Confirm backup venue option',
                        'Arrange transport for VIP guests',
                        'Prepare emergency contact list',
                        'Test backup internet connection',
                        'Print additional name tags',
                        'Prepare welcome speech draft',
                        'Confirm MC availability',
                        'Arrange press conference room',
                        'Prepare feedback survey forms',
                    ]),
                    'priority' => $priority->value,
                    'status' => $status->value,
                    'deadline' => $deadline,
                    'is_completed' => $status->isCompleted(),
                    'responsible_officer' => $hodNames[$dept->name] ?? fake()->name(),
                    'assigned_to' => null,
                    'completed_at' => $completedAt,
                    'is_escalated' => $status->isEscalated(),
                    'escalated_at' => $status->isEscalated() ? now()->subDays(fake()->numberBetween(1, 3))->format('Y-m-d H:i:s') : null,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ];
            }
        }

        // Bulk insert all requirements at once
        foreach (array_chunk($requirementsToInsert, 100) as $chunk) {
            Requirement::insert($chunk);
        }

        // ── 5. Page Visits ────────────────────────────────────────────
        $this->command->info('  → Page visits');
        $paths = ['/', '/events', '/admin', '/admin/events', '/admin/users', '/admin/staff'];
        $allUsers = User::where('is_approved', true)->get();

        for ($i = 0; $i < 120; $i++) {
            $user = fake()->boolean(70) ? $allUsers->random() : null;
            $visitedAt = fake()->dateTimeBetween('-7 days', 'now');

            PageVisit::create([
                'user_id' => $user?->id,
                'path' => fake()->randomElement($paths),
                'method' => 'GET',
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'visited_at' => $visitedAt,
            ]);
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('  Admin:    '.config('admin.email').' / password');
        $this->command->info('  Director: director@ksg.ac.ke / password');
        $this->command->info('  HODs:     <dept>-hod@ksg.ac.ke / password');
        $this->command->info('  Employee: kevin.omondi@ksg.ac.ke / password');
    }
}
