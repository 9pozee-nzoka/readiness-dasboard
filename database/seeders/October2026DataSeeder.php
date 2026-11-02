<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Department;
use App\Models\Event;
use App\Models\Notification;
use App\Models\OutsourcedService;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Database\Seeder;

class October2026DataSeeder extends Seeder
{
    /**
     * Seed data for October 2026 system restructuring.
     *
     * Creates:
     * - Sample notifications (various types)
     * - Threaded comments on events and requirements
     * - Outsourced services with different statuses
     * - Verified/unverified requirements for HOD workflow
     */
    public function run(): void
    {
        $this->command->info('Seeding October 2026 system data...');

        // ── 1. Verify Requirements (HOD Verification) ──────────────────
        $this->command->info('  → Verifying completed requirements');
        $this->seedVerifiedRequirements();

        // ── 2. Notifications ───────────────────────────────────────────
        $this->command->info('  → Creating notifications');
        $this->seedNotifications();

        // ── 3. Comments & Replies ──────────────────────────────────────
        $this->command->info('  → Creating comments and replies');
        $this->seedComments();

        // ── 4. Outsourced Services ─────────────────────────────────────
        $this->command->info('  → Creating outsourced services');
        $this->seedOutsourcedServices();

        $this->command->info('October 2026 data seeded successfully!');
    }

    /**
     * Verify some completed requirements to demonstrate HOD verification workflow.
     */
    private function seedVerifiedRequirements(): void
    {
        $hods = User::where('role', 'hod')->get();

        foreach ($hods as $hod) {
            // Get completed requirements in HOD's department
            $completedRequirements = Requirement::where('department_id', $hod->department_id)
                ->where('is_completed', true)
                ->where('is_verified', false)
                ->take(fake()->numberBetween(2, 5))
                ->get();

            foreach ($completedRequirements as $requirement) {
                $shouldVerify = fake()->boolean(70); // 70% verification rate

                if ($shouldVerify) {
                    $requirement->verify($hod, fake()->randomElement([
                        'Confirmed completion meets quality standards.',
                        'Verified and approved for readiness calculation.',
                        'Quality check passed. Good work!',
                        'Meets all requirements. Approved.',
                        null, // Some without notes
                    ]));
                }
            }
        }
    }

    /**
     * Create sample notifications for various system events.
     */
    private function seedNotifications(): void
    {
        $employees = User::where('role', 'employee')->get();
        $hods = User::where('role', 'hod')->get();
        $director = User::where('role', 'director')->first();
        $events = Event::with('requirements')->get();

        // Assignment notifications
        foreach ($employees as $employee) {
            $requirements = Requirement::where('assigned_to', $employee->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->take(2)
                ->get();

            foreach ($requirements as $requirement) {
                Notification::create([
                    'user_id' => $employee->id,
                    'type' => 'assignment',
                    'title' => 'New Requirement Assigned',
                    'body' => "You've been assigned: {$requirement->description}",
                    'action_url' => route('events.show', $requirement->event_id),
                    'event_id' => $requirement->event_id,
                    'requirement_id' => $requirement->id,
                    'read_at' => fake()->boolean(30) ? now()->subDays(fake()->numberBetween(1, 3)) : null,
                    'created_at' => now()->subDays(fake()->numberBetween(1, 7)),
                ]);
            }
        }

        // Deadline approaching notifications
        $upcomingRequirements = Requirement::whereDate('deadline', '>=', now())
            ->whereDate('deadline', '<=', now()->addDays(3))
            ->where('is_completed', false)
            ->whereNotNull('assigned_to')
            ->get();

        foreach ($upcomingRequirements as $requirement) {
            Notification::create([
                'user_id' => $requirement->assigned_to,
                'type' => 'deadline_approaching',
                'title' => 'Deadline Approaching',
                'body' => 'Deadline in '.now()->diffInDays($requirement->deadline)." days: {$requirement->description}",
                'action_url' => route('events.show', $requirement->event_id),
                'event_id' => $requirement->event_id,
                'requirement_id' => $requirement->id,
                'read_at' => fake()->boolean(50) ? now()->subHours(fake()->numberBetween(1, 12)) : null,
                'created_at' => now()->subHours(fake()->numberBetween(1, 24)),
            ]);
        }

        // Overdue notifications
        $overdueRequirements = Requirement::whereDate('deadline', '<', now())
            ->where('is_completed', false)
            ->whereNotNull('assigned_to')
            ->take(5)
            ->get();

        foreach ($overdueRequirements as $requirement) {
            Notification::create([
                'user_id' => $requirement->assigned_to,
                'type' => 'overdue',
                'title' => 'Requirement Overdue',
                'body' => 'Overdue by '.now()->diffInDays($requirement->deadline)." days: {$requirement->description}",
                'action_url' => route('events.show', $requirement->event_id),
                'event_id' => $requirement->event_id,
                'requirement_id' => $requirement->id,
                'read_at' => fake()->boolean(80) ? now()->subDays(fake()->numberBetween(1, 2)) : null,
                'created_at' => now()->subDays(fake()->numberBetween(1, 3)),
            ]);
        }

        // Verification requested (to HODs)
        foreach ($hods as $hod) {
            $needsVerification = Requirement::where('department_id', $hod->department_id)
                ->where('is_completed', true)
                ->where('is_verified', false)
                ->take(3)
                ->get();

            foreach ($needsVerification as $requirement) {
                Notification::create([
                    'user_id' => $hod->id,
                    'type' => 'verification_requested',
                    'title' => 'Verification Needed',
                    'body' => "Please verify completed requirement: {$requirement->description}",
                    'action_url' => route('events.show', $requirement->event_id),
                    'event_id' => $requirement->event_id,
                    'requirement_id' => $requirement->id,
                    'read_at' => fake()->boolean(40) ? now()->subHours(fake()->numberBetween(1, 48)) : null,
                    'created_at' => now()->subHours(fake()->numberBetween(1, 72)),
                ]);
            }
        }

        // Verification approved (to employees)
        $verifiedRequirements = Requirement::where('is_verified', true)
            ->whereNotNull('assigned_to')
            ->take(10)
            ->get();

        foreach ($verifiedRequirements as $requirement) {
            $verifier = User::find($requirement->verified_by);
            if ($verifier) {
                Notification::create([
                    'user_id' => $requirement->assigned_to,
                    'type' => 'verification_approved',
                    'title' => 'Requirement Verified',
                    'body' => "{$verifier->name} verified your completion: {$requirement->description}",
                    'action_url' => route('events.show', $requirement->event_id),
                    'event_id' => $requirement->event_id,
                    'requirement_id' => $requirement->id,
                    'read_at' => fake()->boolean(60) ? $requirement->verified_at : null,
                    'created_at' => $requirement->verified_at,
                ]);
            }
        }

        // Service approval notifications (to director)
        if ($director) {
            $pendingServices = OutsourcedService::where('approval_status', 'pending')->take(3)->get();
            foreach ($pendingServices as $service) {
                Notification::create([
                    'user_id' => $director->id,
                    'type' => 'service_approval_needed',
                    'title' => 'Service Approval Required',
                    'body' => "New outsourced service awaiting approval: {$service->service_type} from {$service->provider}",
                    'action_url' => route('admin.outsourced-services.show', $service),
                    'created_at' => $service->created_at,
                ]);
            }
        }

        // Event created notifications
        $recentEvents = Event::orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recentEvents as $event) {
            foreach ($hods as $hod) {
                Notification::create([
                    'user_id' => $hod->id,
                    'type' => 'event_created',
                    'title' => 'New Event Created',
                    'body' => "New event scheduled: {$event->name} on {$event->event_date->format('M d, Y')}",
                    'action_url' => route('events.show', $event),
                    'event_id' => $event->id,
                    'read_at' => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(1, 2)) : null,
                    'created_at' => $event->created_at,
                ]);
            }
        }
    }

    /**
     * Create sample comments with threaded replies.
     */
    private function seedComments(): void
    {
        $users = User::where('is_approved', true)->get();
        $events = Event::take(5)->get();

        // Comments on events
        foreach ($events as $event) {
            $commentCount = fake()->numberBetween(2, 5);

            for ($i = 0; $i < $commentCount; $i++) {
                $author = $users->random();
                $parent = Comment::create([
                    'user_id' => $author->id,
                    'event_id' => $event->id,
                    'message' => fake()->randomElement([
                        'Please confirm the final headcount by end of day.',
                        'Venue setup should be completed by 6 AM on event day.',
                        'Has the catering team confirmed the menu options?',
                        'Security briefing scheduled for tomorrow at 9 AM.',
                        'Transport arrangements finalized. 5 buses confirmed.',
                        'Audio-visual team needs 2 hours setup time.',
                        'Registration desk will open at 7:30 AM.',
                        'Backup internet connection has been tested and working.',
                    ]),
                    'channel' => 'system',
                    'created_at' => now()->subDays(fake()->numberBetween(1, 5)),
                ]);

                // Add replies to some comments
                if (fake()->boolean(60)) {
                    $replyCount = fake()->numberBetween(1, 3);
                    for ($r = 0; $r < $replyCount; $r++) {
                        $replier = $users->random();
                        Comment::create([
                            'user_id' => $replier->id,
                            'event_id' => $event->id,
                            'parent_comment_id' => $parent->id,
                            'message' => fake()->randomElement([
                                'Confirmed. Will update by EOD.',
                                'Already in progress. Should be done in time.',
                                'Yes, all confirmed with catering manager.',
                                'Noted. Will attend the briefing.',
                                'Great! Thanks for coordinating.',
                                'Acknowledged. Team is ready.',
                                'Perfect timing. See you there.',
                                'All set on our end.',
                            ]),
                            'channel' => 'system',
                            'created_at' => now()->subDays(fake()->numberBetween(0, 4)),
                        ]);
                    }
                }
            }
        }

        // Comments on requirements
        $requirements = Requirement::whereIn('status', ['in_progress'])
            ->take(8)
            ->get();

        foreach ($requirements as $requirement) {
            $author = $requirement->assigned_to ? User::find($requirement->assigned_to) : $users->random();
            if ($author) {
                $parent = Comment::create([
                    'user_id' => $author->id,
                    'requirement_id' => $requirement->id,
                    'message' => fake()->randomElement([
                        'Working on this. Need approval from finance for budget.',
                        'Waiting on vendor quote. Should have it by tomorrow.',
                        'Blocked by procurement process. Escalating to HOD.',
                        'In progress. About 70% complete.',
                        'Need clarification on specifications before proceeding.',
                        'External consultant has been engaged. Awaiting report.',
                        'Equipment delivery delayed by 2 days. Adjusting timeline.',
                        'Coordinating with facilities team for venue modifications.',
                    ]),
                    'channel' => 'system',
                    'created_at' => now()->subDays(fake()->numberBetween(1, 3)),
                ]);

                // HOD reply on in-progress items
                if ($requirement->status === 'in_progress') {
                    $hod = User::where('department_id', $requirement->department_id)
                        ->where('role', 'hod')
                        ->first();

                    if ($hod) {
                        Comment::create([
                            'user_id' => $hod->id,
                            'requirement_id' => $requirement->id,
                            'parent_comment_id' => $parent->id,
                            'message' => fake()->randomElement([
                                'I will follow up with the relevant department head.',
                                'Budget approval in progress. Should be resolved this week.',
                                'Escalating to Director for urgent resolution.',
                                'Will expedite the procurement process. Keep me updated.',
                            ]),
                            'channel' => 'system',
                            'created_at' => now()->subDays(fake()->numberBetween(0, 2)),
                        ]);
                    }
                }
            }
        }

        // Simulate some email/WhatsApp replies (with external IDs)
        $events->take(2)->each(function ($event) use ($users) {
            $author = $users->random();
            $parent = Comment::create([
                'user_id' => $author->id,
                'event_id' => $event->id,
                'message' => 'Event schedule looks good. Please confirm transport arrangements.',
                'channel' => 'system',
                'external_reference' => 'email-'.fake()->uuid(),
                'created_at' => now()->subDays(2),
            ]);

            // Email reply
            Comment::create([
                'user_id' => $users->random()->id,
                'event_id' => $event->id,
                'parent_comment_id' => $parent->id,
                'message' => 'Transport confirmed. 3 buses departing at 7 AM.',
                'channel' => 'email',
                'external_reference' => 'email-reply-'.fake()->uuid(),
                'sender_identifier' => $author->email,
                'created_at' => now()->subDays(1),
            ]);
        });
    }

    /**
     * Create sample outsourced services with various statuses.
     */
    private function seedOutsourcedServices(): void
    {
        $events = Event::whereDate('event_date', '>=', now())->take(6)->get();

        // If no upcoming events, use recent events instead
        if ($events->isEmpty()) {
            $events = Event::orderBy('event_date', 'desc')->take(6)->get();
        }

        // If still no events, skip outsourced services seeding
        if ($events->isEmpty()) {
            $this->command->warn('  ⚠ No events found. Skipping outsourced services seeding.');

            return;
        }

        $departments = Department::where('is_active', true)->get();
        $director = User::where('role', 'director')->first();
        $hods = User::where('role', 'hod')->get();

        $serviceData = [
            // Pending approval services
            [
                'provider' => 'Elite Security Services Ltd',
                'service_type' => 'Additional Security Personnel',
                'type' => 'hiring',
                'reason' => 'High-profile government delegation requires enhanced security.',
                'status' => 'requested',
                'approval_status' => 'pending',
                'cost' => 85000.00,
                'notes' => 'Need 8 additional security officers for 3 days.',
            ],
            [
                'provider' => 'Tech Solutions Kenya',
                'service_type' => 'Audio-Visual Equipment Rental',
                'type' => 'outsourced_service',
                'reason' => 'In-house equipment insufficient for large conference hall.',
                'status' => 'requested',
                'approval_status' => 'pending',
                'cost' => 120000.00,
                'notes' => 'Professional PA system, projectors, and lighting for 300+ participants.',
            ],
            [
                'provider' => 'Green Catering Services',
                'service_type' => 'Specialized Catering',
                'type' => 'outsourced_service',
                'reason' => 'International delegates with specific dietary requirements.',
                'status' => 'requested',
                'approval_status' => 'pending',
                'cost' => 450000.00,
                'notes' => 'Halal, vegan, and gluten-free menu options for 120 participants over 3 days.',
            ],

            // Approved and active services
            [
                'provider' => 'Professional Interpreters Association',
                'service_type' => 'Simultaneous Translation Services',
                'type' => 'outsourced_service',
                'reason' => 'Regional conference with French and Swahili translation needs.',
                'status' => 'confirmed',
                'approval_status' => 'approved',
                'cost' => 180000.00,
                'notes' => '3 professional interpreters for 2 days.',
            ],
            [
                'provider' => 'SafeRide Transport Ltd',
                'service_type' => 'VIP Transport Services',
                'type' => 'outsourced_service',
                'reason' => 'Airport transfers for cabinet secretaries and ambassadors.',
                'status' => 'approved',
                'approval_status' => 'approved',
                'cost' => 95000.00,
                'notes' => '5 executive vehicles with professional drivers.',
            ],
            [
                'provider' => 'Dr. Jane Ouma - Public Policy Consultant',
                'service_type' => 'Expert Facilitation',
                'type' => 'hiring',
                'reason' => 'Specialized expertise in devolution policy required for workshop.',
                'status' => 'confirmed',
                'approval_status' => 'approved',
                'cost' => 250000.00,
                'notes' => 'Lead facilitator for 2-day policy workshop. Highly recommended by CoG.',
            ],

            // Completed services
            [
                'provider' => 'Modern Print Solutions',
                'service_type' => 'Conference Materials Printing',
                'type' => 'outsourced_service',
                'reason' => 'High-volume printing required beyond internal capacity.',
                'status' => 'completed',
                'approval_status' => 'approved',
                'cost' => 45000.00,
                'notes' => 'Printed 500 participant manuals and certificates.',
            ],
            [
                'provider' => 'EventPro Photography',
                'service_type' => 'Professional Photography & Videography',
                'type' => 'outsourced_service',
                'reason' => 'Official documentation of graduation ceremony.',
                'status' => 'completed',
                'approval_status' => 'approved',
                'cost' => 75000.00,
                'notes' => 'Full day coverage with edited highlights video.',
            ],

            // Rejected service
            [
                'provider' => 'Luxury Decor Services',
                'service_type' => 'Premium Event Decoration',
                'type' => 'outsourced_service',
                'reason' => 'Enhanced aesthetic appeal for VIP ceremony.',
                'status' => 'cancelled',
                'approval_status' => 'rejected',
                'cost' => 200000.00,
                'notes' => 'Budget allocated to more critical services.',
            ],
        ];

        foreach ($serviceData as $index => $data) {
            $event = $events->get($index % $events->count());
            $department = $departments->random();
            $responsible = $hods->where('department_id', $department->id)->first() ?? $hods->random();

            $startDate = $event->event_date->copy()->subDays(fake()->numberBetween(1, 3));
            $endDate = $data['type'] === 'hiring'
                ? $event->event_date->copy()->addDays(fake()->numberBetween(1, 5))
                : $event->event_date->copy();

            $service = OutsourcedService::create([
                'event_id' => $event->id,
                'department_id' => $department->id,
                'provider' => $data['provider'],
                'service_type' => $data['service_type'],
                'type' => $data['type'],
                'reason' => $data['reason'],
                'status' => $data['status'],
                'approval_status' => $data['approval_status'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'responsible_user_id' => $responsible->id,
                'cost' => $data['cost'],
                'notes' => $data['notes'],
                'created_at' => now()->subDays(fake()->numberBetween(5, 15)),
            ]);
        }
    }
}
