<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\EventTypeRequirement;
use Illuminate\Database\Seeder;

class ConferenceRequirementsSeeder extends Seeder
{
    public function run(): void
    {
        $depts = Department::whereIn('name', [
            'Administration',
            'ICT',
            'Catering',
            'Facilities',
            'Security',
            'Finance',
            'Logistics',
            'Communications',
            'Protocol',
            'Human Resources',
        ])->pluck('id', 'name');

        $requirements = [
            // Administration
            ['dept' => 'Administration', 'desc' => 'Prepare conference programme booklet', 'priority' => 'critical'],
            ['dept' => 'Administration', 'desc' => 'Prepare delegate registration desk', 'priority' => 'critical'],
            ['dept' => 'Administration', 'desc' => 'Prepare conference badges and lanyards', 'priority' => 'high'],
            ['dept' => 'Administration', 'desc' => 'Send conference invitations to all delegates', 'priority' => 'critical'],
            ['dept' => 'Administration', 'desc' => 'Prepare attendance registers', 'priority' => 'high'],
            ['dept' => 'Administration', 'desc' => 'Prepare conference folders and stationery', 'priority' => 'high'],
            ['dept' => 'Administration', 'desc' => 'Coordinate delegate confirmations', 'priority' => 'high'],

            // ICT
            ['dept' => 'ICT', 'desc' => 'Set up main conference hall AV system', 'priority' => 'critical'],
            ['dept' => 'ICT', 'desc' => 'Test all microphones and sound system', 'priority' => 'critical'],
            ['dept' => 'ICT', 'desc' => 'Set up projectors and screens in all conference rooms', 'priority' => 'critical'],
            ['dept' => 'ICT', 'desc' => 'Provide technical support team during conference', 'priority' => 'high'],
            ['dept' => 'ICT', 'desc' => 'Set up live streaming equipment (if required)', 'priority' => 'medium'],
            ['dept' => 'ICT', 'desc' => 'Ensure WiFi coverage in all conference areas', 'priority' => 'high'],
            ['dept' => 'ICT', 'desc' => 'Prepare conference materials on shared drive', 'priority' => 'high'],
            ['dept' => 'ICT', 'desc' => 'Set up video conferencing for hybrid sessions', 'priority' => 'medium'],

            // Catering
            ['dept' => 'Catering', 'desc' => 'Confirm delegate dietary requirements', 'priority' => 'high'],
            ['dept' => 'Catering', 'desc' => 'Arrange breakfast for residential delegates', 'priority' => 'high'],
            ['dept' => 'Catering', 'desc' => 'Arrange morning and afternoon tea breaks', 'priority' => 'critical'],
            ['dept' => 'Catering', 'desc' => 'Arrange buffet lunch for all delegates', 'priority' => 'critical'],
            ['dept' => 'Catering', 'desc' => 'Arrange gala dinner (if applicable)', 'priority' => 'medium'],
            ['dept' => 'Catering', 'desc' => 'Provide water stations in conference halls', 'priority' => 'high'],

            // Facilities
            ['dept' => 'Facilities', 'desc' => 'Set up main conference hall seating', 'priority' => 'critical'],
            ['dept' => 'Facilities', 'desc' => 'Set up breakout rooms for parallel sessions', 'priority' => 'high'],
            ['dept' => 'Facilities', 'desc' => 'Prepare high table and speaker podium', 'priority' => 'high'],
            ['dept' => 'Facilities', 'desc' => 'Set up exhibition area (if required)', 'priority' => 'medium'],
            ['dept' => 'Facilities', 'desc' => 'Ensure adequate lighting and ventilation', 'priority' => 'high'],
            ['dept' => 'Facilities', 'desc' => 'Prepare signage for conference venues', 'priority' => 'high'],
            ['dept' => 'Facilities', 'desc' => 'Set up flip charts in breakout rooms', 'priority' => 'medium'],

            // Security
            ['dept' => 'Security', 'desc' => 'Brief security team on conference access control', 'priority' => 'high'],
            ['dept' => 'Security', 'desc' => 'Prepare delegate access list', 'priority' => 'high'],
            ['dept' => 'Security', 'desc' => 'Provide security at registration desk', 'priority' => 'high'],
            ['dept' => 'Security', 'desc' => 'Coordinate VIP security arrangements', 'priority' => 'critical'],
            ['dept' => 'Security', 'desc' => 'Monitor parking arrangements', 'priority' => 'medium'],

            // Finance
            ['dept' => 'Finance', 'desc' => 'Confirm conference budget allocation', 'priority' => 'critical'],
            ['dept' => 'Finance', 'desc' => 'Process payments to facilitators/speakers', 'priority' => 'high'],
            ['dept' => 'Finance', 'desc' => 'Process payments to service providers', 'priority' => 'high'],
            ['dept' => 'Finance', 'desc' => 'Prepare delegate allowances', 'priority' => 'high'],
            ['dept' => 'Finance', 'desc' => 'Track conference expenditure', 'priority' => 'medium'],

            // Logistics
            ['dept' => 'Logistics', 'desc' => 'Arrange transport for delegates', 'priority' => 'high'],
            ['dept' => 'Logistics', 'desc' => 'Coordinate airport pickups for external delegates', 'priority' => 'medium'],
            ['dept' => 'Logistics', 'desc' => 'Arrange accommodation for residential delegates', 'priority' => 'critical'],
            ['dept' => 'Logistics', 'desc' => 'Deliver conference materials to venue', 'priority' => 'high'],
            ['dept' => 'Logistics', 'desc' => 'Arrange transport for guest speakers', 'priority' => 'high'],

            // Communications
            ['dept' => 'Communications', 'desc' => 'Prepare conference press release', 'priority' => 'medium'],
            ['dept' => 'Communications', 'desc' => 'Arrange photography and videography', 'priority' => 'high'],
            ['dept' => 'Communications', 'desc' => 'Prepare social media coverage plan', 'priority' => 'low'],
            ['dept' => 'Communications', 'desc' => 'Design conference banners and branding', 'priority' => 'high'],
            ['dept' => 'Communications', 'desc' => 'Coordinate media coverage (if applicable)', 'priority' => 'low'],
            ['dept' => 'Communications', 'desc' => 'Prepare post-conference report', 'priority' => 'low'],

            // Protocol
            ['dept' => 'Protocol', 'desc' => 'Coordinate programme flow and timings', 'priority' => 'critical'],
            ['dept' => 'Protocol', 'desc' => 'Brief master of ceremony', 'priority' => 'high'],
            ['dept' => 'Protocol', 'desc' => 'Coordinate VIP seating arrangements', 'priority' => 'high'],
            ['dept' => 'Protocol', 'desc' => 'Prepare speeches and remarks', 'priority' => 'critical'],
            ['dept' => 'Protocol', 'desc' => 'Coordinate national anthem and prayers', 'priority' => 'medium'],
            ['dept' => 'Protocol', 'desc' => 'Manage conference opening and closing ceremonies', 'priority' => 'high'],

            // Human Resources
            ['dept' => 'Human Resources', 'desc' => 'Brief all conference facilitators', 'priority' => 'high'],
            ['dept' => 'Human Resources', 'desc' => 'Confirm speaker and panelist availability', 'priority' => 'critical'],
            ['dept' => 'Human Resources', 'desc' => 'Arrange staff duty roster for conference days', 'priority' => 'high'],
            ['dept' => 'Human Resources', 'desc' => 'Coordinate volunteer briefing', 'priority' => 'medium'],
            ['dept' => 'Human Resources', 'desc' => 'Prepare certificates for participants', 'priority' => 'medium'],
        ];

        foreach ($requirements as $req) {
            EventTypeRequirement::create([
                'event_type' => 'Conference',
                'department_id' => $depts[$req['dept']],
                'description' => $req['desc'],
                'priority' => $req['priority'],
            ]);
        }

        $this->command->info('✅ Conference requirements seeded: '.count($requirements).' requirements');
    }
}
