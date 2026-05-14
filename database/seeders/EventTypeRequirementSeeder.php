<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\EventTypeRequirement;
use Illuminate\Database\Seeder;

class EventTypeRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $depts = Department::pluck('id', 'slug');

        /**
         * Template library.
         * Format: event_type → department_slug → [ [description, priority], ... ]
         * Deadlines are set manually by the admin per template — null by default.
         */
        $templates = [
            'Meeting' => [
                'ict' => [
                    ['Set up audio-visual equipment',        'critical'],
                    ['Test internet connectivity',           'high'],
                    ['Configure video conferencing',         'high'],
                ],
                'finance' => [
                    ['Prepare budget summary',               'high'],
                    ['Confirm petty cash float',             'medium'],
                ],
                'administration' => [
                    ['Prepare agenda and briefing documents', 'critical'],
                    ['Confirm attendance list',              'high'],
                    ['Send invitations and reminders',       'high'],
                    ['Prepare minutes template',             'medium'],
                ],
                'security' => [
                    ['Brief security on access control',     'high'],
                    ['Prepare visitor log sheets',           'medium'],
                ],
                'logistics' => [
                    ['Coordinate transport and parking',     'medium'],
                    ['Arrange equipment delivery',           'medium'],
                ],
                'communications' => [
                    ['Prepare press releases',               'medium'],
                    ['Set up social media coverage',         'low'],
                ],
                'facilities' => [
                    ['Prepare venue layout and seating',     'high'],
                    ['Ensure venue cleanliness',             'medium'],
                    ['Check lighting and HVAC',              'medium'],
                ],
                'human-resources' => [
                    ['Confirm staff duty roster',            'high'],
                    ['Prepare name tags',                    'medium'],
                ],
                'protocol' => [
                    ['Confirm VIP protocol arrangements',    'critical'],
                    ['Prepare VIP seating plan',             'high'],
                ],
                'catering' => [
                    ['Arrange refreshments',                 'high'],
                    ['Confirm dietary requirements',         'medium'],
                ],
            ],

            'Training' => [
                'ict' => [
                    ['Set up training computers',                   'critical'],
                    ['Test projector and screen',                   'high'],
                    ['Prepare training materials on shared drive',  'high'],
                ],
                'finance' => [
                    ['Process trainer payments',                    'high'],
                    ['Confirm training budget',                     'medium'],
                ],
                'administration' => [
                    ['Prepare training schedule',                   'critical'],
                    ['Send participant invitations',                'high'],
                    ['Prepare attendance register',                 'medium'],
                ],
                'security' => [
                    ['Brief security on participant access',        'medium'],
                    ['Prepare visitor log sheets',                  'low'],
                ],
                'logistics' => [
                    ['Arrange transport for participants',          'medium'],
                    ['Deliver training materials',                  'high'],
                ],
                'communications' => [
                    ['Prepare training announcement',               'medium'],
                    ['Arrange photography',                         'low'],
                ],
                'facilities' => [
                    ['Set up training room layout',                 'high'],
                    ['Ensure whiteboard and markers available',     'medium'],
                    ['Check room temperature',                      'low'],
                ],
                'human-resources' => [
                    ['Confirm participant list',                    'high'],
                    ['Prepare certificates',                        'medium'],
                    ['Brief event volunteers',                      'medium'],
                ],
                'protocol' => [
                    ['Coordinate MC and programme flow',            'medium'],
                ],
                'catering' => [
                    ['Arrange tea breaks and lunch',                'high'],
                    ['Confirm dietary requirements',                'medium'],
                ],
            ],

            'Ceremony' => [
                'ict' => [
                    ['Set up PA system and microphones',            'critical'],
                    ['Test audio-visual equipment',                 'critical'],
                    ['Arrange live streaming if required',          'high'],
                ],
                'finance' => [
                    ['Prepare event budget',                        'high'],
                    ['Process vendor payments',                     'critical'],
                    ['Confirm petty cash float',                    'medium'],
                ],
                'administration' => [
                    ['Prepare programme booklet',                   'critical'],
                    ['Confirm guest list',                          'high'],
                    ['Send formal invitations',                     'high'],
                ],
                'security' => [
                    ['Set up perimeter security',                   'critical'],
                    ['Coordinate with external security',           'high'],
                    ['Prepare VIP access control',                  'critical'],
                ],
                'logistics' => [
                    ['Coordinate transport and parking',            'high'],
                    ['Arrange stage and podium setup',              'critical'],
                    ['Confirm supplier deliveries',                 'high'],
                ],
                'communications' => [
                    ['Arrange photography and videography',         'high'],
                    ['Prepare press releases',                      'medium'],
                    ['Set up social media coverage',                'medium'],
                    ['Prepare signage and banners',                 'high'],
                ],
                'facilities' => [
                    ['Prepare venue layout and seating',            'critical'],
                    ['Arrange flowers and decorations',             'high'],
                    ['Ensure venue cleanliness',                    'high'],
                    ['Check lighting',                              'high'],
                ],
                'human-resources' => [
                    ['Confirm staff duty roster',                   'high'],
                    ['Brief event volunteers',                      'high'],
                    ['Prepare name tags and welcome packs',         'medium'],
                ],
                'protocol' => [
                    ['Confirm VIP protocol arrangements',           'critical'],
                    ['Prepare VIP seating plan',                    'critical'],
                    ['Coordinate MC and programme flow',            'high'],
                    ['Arrange VIP parking',                         'high'],
                ],
                'catering' => [
                    ['Arrange catering and refreshments',           'critical'],
                    ['Confirm dietary requirements',                'high'],
                    ['Set up catering stations',                    'high'],
                    ['Coordinate catering staff',                   'medium'],
                ],
            ],

            'Forum' => [
                'ict' => [
                    ['Set up audio-visual equipment',               'critical'],
                    ['Test internet connectivity',                  'high'],
                    ['Configure presentation systems',              'high'],
                ],
                'finance' => [
                    ['Prepare event budget',                        'high'],
                    ['Process speaker payments',                    'high'],
                ],
                'administration' => [
                    ['Prepare agenda',                              'critical'],
                    ['Confirm speaker list',                        'high'],
                    ['Send invitations',                            'high'],
                    ['Prepare discussion papers',                   'medium'],
                ],
                'security' => [
                    ['Brief security on access control',            'high'],
                    ['Prepare visitor log sheets',                  'medium'],
                ],
                'logistics' => [
                    ['Coordinate transport',                        'medium'],
                    ['Arrange equipment delivery',                  'medium'],
                ],
                'communications' => [
                    ['Prepare press releases',                      'medium'],
                    ['Arrange photography',                         'low'],
                    ['Set up social media coverage',                'medium'],
                ],
                'facilities' => [
                    ['Prepare venue layout',                        'high'],
                    ['Ensure venue cleanliness',                    'medium'],
                    ['Check lighting and HVAC',                     'medium'],
                ],
                'human-resources' => [
                    ['Confirm staff duty roster',                   'high'],
                    ['Brief event volunteers',                      'medium'],
                ],
                'protocol' => [
                    ['Coordinate MC and programme flow',            'high'],
                    ['Confirm VIP arrangements',                    'high'],
                ],
                'catering' => [
                    ['Arrange refreshments',                        'high'],
                    ['Confirm dietary requirements',                'medium'],
                ],
            ],

            'Review' => [
                'ict' => [
                    ['Set up presentation equipment',               'high'],
                    ['Prepare shared document access',              'medium'],
                ],
                'finance' => [
                    ['Prepare financial reports',                   'critical'],
                    ['Compile budget vs actual analysis',           'high'],
                ],
                'administration' => [
                    ['Prepare review agenda',                       'high'],
                    ['Compile performance reports',                 'high'],
                    ['Send meeting invitations',                    'medium'],
                ],
                'security' => [
                    ['Brief security on access',                    'low'],
                ],
                'logistics' => [
                    ['Arrange transport',                           'low'],
                ],
                'communications' => [
                    ['Prepare summary report for distribution',     'medium'],
                ],
                'facilities' => [
                    ['Prepare meeting room',                        'high'],
                    ['Ensure refreshments available',               'medium'],
                ],
                'human-resources' => [
                    ['Compile HR performance data',                 'high'],
                    ['Prepare attendance records',                  'medium'],
                ],
                'protocol' => [
                    ['Coordinate meeting flow',                     'medium'],
                ],
                'catering' => [
                    ['Arrange refreshments',                        'medium'],
                ],
            ],

            'Retreat' => [
                'ict' => [
                    ['Set up presentation equipment',               'high'],
                    ['Ensure Wi-Fi connectivity at venue',          'critical'],
                ],
                'finance' => [
                    ['Prepare retreat budget',                      'critical'],
                    ['Process accommodation payments',              'critical'],
                    ['Confirm per diem allowances',                 'high'],
                ],
                'administration' => [
                    ['Prepare retreat programme',                   'critical'],
                    ['Confirm participant list',                    'high'],
                    ['Book accommodation',                          'critical'],
                ],
                'security' => [
                    ['Coordinate security at retreat venue',        'high'],
                ],
                'logistics' => [
                    ['Arrange transport to venue',                  'critical'],
                    ['Coordinate luggage handling',                 'medium'],
                ],
                'communications' => [
                    ['Prepare retreat communications',              'medium'],
                    ['Arrange photography',                         'low'],
                ],
                'facilities' => [
                    ['Confirm venue facilities',                    'high'],
                    ['Arrange team-building equipment',             'medium'],
                ],
                'human-resources' => [
                    ['Confirm participant list',                    'high'],
                    ['Prepare team-building activities',            'high'],
                    ['Brief facilitators',                          'high'],
                ],
                'protocol' => [
                    ['Coordinate programme flow',                   'high'],
                    ['Arrange VIP accommodation',                   'high'],
                ],
                'catering' => [
                    ['Arrange all meals',                           'critical'],
                    ['Confirm dietary requirements',                'high'],
                    ['Coordinate catering staff',                   'medium'],
                ],
            ],

            'Workshop' => [
                'ict' => [
                    ['Set up computers and software',               'critical'],
                    ['Test projector',                              'high'],
                    ['Prepare workshop materials on shared drive',  'high'],
                ],
                'finance' => [
                    ['Process facilitator payments',                'high'],
                    ['Confirm workshop budget',                     'medium'],
                ],
                'administration' => [
                    ['Prepare workshop schedule',                   'critical'],
                    ['Send participant invitations',                'high'],
                    ['Prepare attendance register',                 'medium'],
                ],
                'security' => [
                    ['Brief security on participant access',        'medium'],
                ],
                'logistics' => [
                    ['Deliver workshop materials',                  'high'],
                    ['Arrange transport',                           'medium'],
                ],
                'communications' => [
                    ['Prepare workshop announcement',               'medium'],
                    ['Arrange photography',                         'low'],
                ],
                'facilities' => [
                    ['Set up workshop room',                        'high'],
                    ['Ensure flip charts and markers available',    'medium'],
                ],
                'human-resources' => [
                    ['Confirm participant list',                    'high'],
                    ['Brief facilitators',                          'high'],
                ],
                'protocol' => [
                    ['Coordinate programme flow',                   'medium'],
                ],
                'catering' => [
                    ['Arrange tea breaks',                          'high'],
                    ['Confirm dietary requirements',                'medium'],
                ],
            ],
        ];

        $order = 0;
        foreach ($templates as $eventType => $deptMap) {
            foreach ($deptMap as $slug => $requirements) {
                $deptId = $depts[$slug] ?? null;
                if (! $deptId) {
                    continue;
                }
                foreach ($requirements as [$description, $priority]) {
                    EventTypeRequirement::firstOrCreate(
                        [
                            'event_type' => $eventType,
                            'department_id' => $deptId,
                            'description' => $description,
                        ],
                        [
                            'priority' => $priority,
                            'deadline' => null, // admin sets deadlines manually
                            'sort_order' => $order++,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
