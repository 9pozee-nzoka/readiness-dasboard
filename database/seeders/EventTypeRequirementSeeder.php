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
         * Format: event_type → department_slug → [ [desc, priority, deadline_days_before], ... ]
         * priority: critical | high | medium | low
         * deadline_days_before: days before the event date (null = no deadline)
         */
        $templates = [
            'Meeting' => [
                'ict'             => [
                    ['Set up audio-visual equipment',   'critical', 1],
                    ['Test internet connectivity',      'high',     1],
                    ['Configure video conferencing',    'high',     1],
                ],
                'finance'         => [
                    ['Prepare budget summary',          'high',     2],
                    ['Confirm petty cash float',        'medium',   1],
                ],
                'administration'  => [
                    ['Prepare agenda and briefing documents', 'critical', 2],
                    ['Confirm attendance list',               'high',     2],
                    ['Send invitations and reminders',        'high',     3],
                    ['Prepare minutes template',              'medium',   1],
                ],
                'security'        => [
                    ['Brief security on access control', 'high',   1],
                    ['Prepare visitor log sheets',       'medium', 1],
                ],
                'logistics'       => [
                    ['Coordinate transport and parking', 'medium', 2],
                    ['Arrange equipment delivery',       'medium', 2],
                ],
                'communications'  => [
                    ['Prepare press releases',           'medium', 3],
                    ['Set up social media coverage',     'low',    2],
                ],
                'facilities'      => [
                    ['Prepare venue layout and seating', 'high',   1],
                    ['Ensure venue cleanliness',         'medium', 1],
                    ['Check lighting and HVAC',          'medium', 1],
                ],
                'human-resources' => [
                    ['Confirm staff duty roster',        'high',   2],
                    ['Prepare name tags',                'medium', 1],
                ],
                'protocol'        => [
                    ['Confirm VIP protocol arrangements', 'critical', 2],
                    ['Prepare VIP seating plan',          'high',     1],
                ],
                'catering'        => [
                    ['Arrange refreshments',             'high',   1],
                    ['Confirm dietary requirements',     'medium', 2],
                ],
            ],

            'Training' => [
                'ict'             => [
                    ['Set up training computers',                    'critical', 1],
                    ['Test projector and screen',                    'high',     1],
                    ['Prepare training materials on shared drive',   'high',     2],
                ],
                'finance'         => [
                    ['Process trainer payments',         'high',   3],
                    ['Confirm training budget',          'medium', 5],
                ],
                'administration'  => [
                    ['Prepare training schedule',        'critical', 3],
                    ['Send participant invitations',     'high',     5],
                    ['Prepare attendance register',      'medium',   1],
                ],
                'security'        => [
                    ['Brief security on participant access', 'medium', 1],
                    ['Prepare visitor log sheets',           'low',    1],
                ],
                'logistics'       => [
                    ['Arrange transport for participants', 'medium', 2],
                    ['Deliver training materials',         'high',   1],
                ],
                'communications'  => [
                    ['Prepare training announcement',    'medium', 5],
                    ['Arrange photography',              'low',    1],
                ],
                'facilities'      => [
                    ['Set up training room layout',                  'high',   1],
                    ['Ensure whiteboard and markers available',      'medium', 1],
                    ['Check room temperature',                       'low',    1],
                ],
                'human-resources' => [
                    ['Confirm participant list',         'high',   3],
                    ['Prepare certificates',             'medium', 2],
                    ['Brief event volunteers',           'medium', 1],
                ],
                'protocol'        => [
                    ['Coordinate MC and programme flow', 'medium', 2],
                ],
                'catering'        => [
                    ['Arrange tea breaks and lunch',     'high',   1],
                    ['Confirm dietary requirements',     'medium', 2],
                ],
            ],

            'Ceremony' => [
                'ict'             => [
                    ['Set up PA system and microphones',         'critical', 2],
                    ['Test audio-visual equipment',              'critical', 1],
                    ['Arrange live streaming if required',       'high',     3],
                ],
                'finance'         => [
                    ['Prepare event budget',             'high',   7],
                    ['Process vendor payments',          'critical', 3],
                    ['Confirm petty cash float',         'medium', 1],
                ],
                'administration'  => [
                    ['Prepare programme booklet',        'critical', 3],
                    ['Confirm guest list',               'high',     5],
                    ['Send formal invitations',          'high',     7],
                ],
                'security'        => [
                    ['Set up perimeter security',        'critical', 1],
                    ['Coordinate with external security','high',     2],
                    ['Prepare VIP access control',       'critical', 1],
                ],
                'logistics'       => [
                    ['Coordinate transport and parking', 'high',   2],
                    ['Arrange stage and podium setup',   'critical', 2],
                    ['Confirm supplier deliveries',      'high',   3],
                ],
                'communications'  => [
                    ['Arrange photography and videography', 'high',   1],
                    ['Prepare press releases',              'medium', 5],
                    ['Set up social media coverage',        'medium', 3],
                    ['Prepare signage and banners',         'high',   3],
                ],
                'facilities'      => [
                    ['Prepare venue layout and seating', 'critical', 2],
                    ['Arrange flowers and decorations',  'high',     1],
                    ['Ensure venue cleanliness',         'high',     1],
                    ['Check lighting',                   'high',     1],
                ],
                'human-resources' => [
                    ['Confirm staff duty roster',        'high',   3],
                    ['Brief event volunteers',           'high',   1],
                    ['Prepare name tags and welcome packs', 'medium', 2],
                ],
                'protocol'        => [
                    ['Confirm VIP protocol arrangements', 'critical', 3],
                    ['Prepare VIP seating plan',          'critical', 2],
                    ['Coordinate MC and programme flow',  'high',     2],
                    ['Arrange VIP parking',               'high',     1],
                ],
                'catering'        => [
                    ['Arrange catering and refreshments', 'critical', 2],
                    ['Confirm dietary requirements',      'high',     3],
                    ['Set up catering stations',          'high',     1],
                    ['Coordinate catering staff',         'medium',   1],
                ],
            ],

            'Forum' => [
                'ict'             => [
                    ['Set up audio-visual equipment',    'critical', 1],
                    ['Test internet connectivity',       'high',     1],
                    ['Configure presentation systems',   'high',     1],
                ],
                'finance'         => [
                    ['Prepare event budget',             'high',   5],
                    ['Process speaker payments',         'high',   3],
                ],
                'administration'  => [
                    ['Prepare agenda',                   'critical', 3],
                    ['Confirm speaker list',             'high',     5],
                    ['Send invitations',                 'high',     7],
                    ['Prepare discussion papers',        'medium',   3],
                ],
                'security'        => [
                    ['Brief security on access control', 'high',   1],
                    ['Prepare visitor log sheets',       'medium', 1],
                ],
                'logistics'       => [
                    ['Coordinate transport',             'medium', 2],
                    ['Arrange equipment delivery',       'medium', 2],
                ],
                'communications'  => [
                    ['Prepare press releases',           'medium', 5],
                    ['Arrange photography',              'low',    1],
                    ['Set up social media coverage',     'medium', 3],
                ],
                'facilities'      => [
                    ['Prepare venue layout',             'high',   1],
                    ['Ensure venue cleanliness',         'medium', 1],
                    ['Check lighting and HVAC',          'medium', 1],
                ],
                'human-resources' => [
                    ['Confirm staff duty roster',        'high',   2],
                    ['Brief event volunteers',           'medium', 1],
                ],
                'protocol'        => [
                    ['Coordinate MC and programme flow', 'high',   2],
                    ['Confirm VIP arrangements',         'high',   2],
                ],
                'catering'        => [
                    ['Arrange refreshments',             'high',   1],
                    ['Confirm dietary requirements',     'medium', 2],
                ],
            ],

            'Review' => [
                'ict'             => [
                    ['Set up presentation equipment',    'high',   1],
                    ['Prepare shared document access',   'medium', 2],
                ],
                'finance'         => [
                    ['Prepare financial reports',        'critical', 3],
                    ['Compile budget vs actual analysis','high',     3],
                ],
                'administration'  => [
                    ['Prepare review agenda',            'high',   2],
                    ['Compile performance reports',      'high',   3],
                    ['Send meeting invitations',         'medium', 5],
                ],
                'security'        => [
                    ['Brief security on access',         'low',    1],
                ],
                'logistics'       => [
                    ['Arrange transport',                'low',    2],
                ],
                'communications'  => [
                    ['Prepare summary report for distribution', 'medium', 2],
                ],
                'facilities'      => [
                    ['Prepare meeting room',             'high',   1],
                    ['Ensure refreshments available',    'medium', 1],
                ],
                'human-resources' => [
                    ['Compile HR performance data',      'high',   3],
                    ['Prepare attendance records',       'medium', 2],
                ],
                'protocol'        => [
                    ['Coordinate meeting flow',          'medium', 1],
                ],
                'catering'        => [
                    ['Arrange refreshments',             'medium', 1],
                ],
            ],

            'Retreat' => [
                'ict'             => [
                    ['Set up presentation equipment',   'high',   2],
                    ['Ensure Wi-Fi connectivity at venue', 'critical', 3],
                ],
                'finance'         => [
                    ['Prepare retreat budget',           'critical', 7],
                    ['Process accommodation payments',   'critical', 5],
                    ['Confirm per diem allowances',      'high',     3],
                ],
                'administration'  => [
                    ['Prepare retreat programme',        'critical', 5],
                    ['Confirm participant list',         'high',     7],
                    ['Book accommodation',               'critical', 7],
                ],
                'security'        => [
                    ['Coordinate security at retreat venue', 'high', 2],
                ],
                'logistics'       => [
                    ['Arrange transport to venue',       'critical', 3],
                    ['Coordinate luggage handling',      'medium',   1],
                ],
                'communications'  => [
                    ['Prepare retreat communications',   'medium', 5],
                    ['Arrange photography',              'low',    1],
                ],
                'facilities'      => [
                    ['Confirm venue facilities',         'high',   5],
                    ['Arrange team-building equipment',  'medium', 3],
                ],
                'human-resources' => [
                    ['Confirm participant list',         'high',   7],
                    ['Prepare team-building activities', 'high',   5],
                    ['Brief facilitators',               'high',   2],
                ],
                'protocol'        => [
                    ['Coordinate programme flow',        'high',   3],
                    ['Arrange VIP accommodation',        'high',   5],
                ],
                'catering'        => [
                    ['Arrange all meals',                'critical', 3],
                    ['Confirm dietary requirements',     'high',     5],
                    ['Coordinate catering staff',        'medium',   2],
                ],
            ],

            'Workshop' => [
                'ict'             => [
                    ['Set up computers and software',   'critical', 1],
                    ['Test projector',                  'high',     1],
                    ['Prepare workshop materials on shared drive', 'high', 2],
                ],
                'finance'         => [
                    ['Process facilitator payments',    'high',   3],
                    ['Confirm workshop budget',         'medium', 5],
                ],
                'administration'  => [
                    ['Prepare workshop schedule',       'critical', 3],
                    ['Send participant invitations',    'high',     5],
                    ['Prepare attendance register',     'medium',   1],
                ],
                'security'        => [
                    ['Brief security on participant access', 'medium', 1],
                ],
                'logistics'       => [
                    ['Deliver workshop materials',      'high',   1],
                    ['Arrange transport',               'medium', 2],
                ],
                'communications'  => [
                    ['Prepare workshop announcement',   'medium', 5],
                    ['Arrange photography',             'low',    1],
                ],
                'facilities'      => [
                    ['Set up workshop room',            'high',   1],
                    ['Ensure flip charts and markers available', 'medium', 1],
                ],
                'human-resources' => [
                    ['Confirm participant list',        'high',   3],
                    ['Brief facilitators',              'high',   2],
                ],
                'protocol'        => [
                    ['Coordinate programme flow',       'medium', 2],
                ],
                'catering'        => [
                    ['Arrange tea breaks',              'high',   1],
                    ['Confirm dietary requirements',    'medium', 2],
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
                foreach ($requirements as [$description, $priority, $deadlineDaysBefore]) {
                    EventTypeRequirement::firstOrCreate(
                        [
                            'event_type'    => $eventType,
                            'department_id' => $deptId,
                            'description'   => $description,
                        ],
                        [
                            'priority'             => $priority,
                            'deadline_days_before' => $deadlineDaysBefore,
                            'sort_order'           => $order++,
                            'is_active'            => true,
                        ]
                    );
                }
            }
        }
    }
}
