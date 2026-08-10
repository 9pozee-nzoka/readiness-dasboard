<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'ICT', 'color' => '#6366f1'],
            ['name' => 'Finance', 'color' => '#0ea5e9'],
            ['name' => 'Administration', 'color' => '#8b5cf6'],
            ['name' => 'Security', 'color' => '#ef4444'],
            ['name' => 'Logistics', 'color' => '#f97316'],
            ['name' => 'Communications', 'color' => '#14b8a6'],
            ['name' => 'Facilities', 'color' => '#84cc16'],
            ['name' => 'Human Resources', 'color' => '#ec4899'],
            ['name' => 'Protocol', 'color' => '#f59e0b'],
            ['name' => 'Catering', 'color' => '#10b981'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['slug' => Str::slug($department['name'])],
                [
                    'name' => $department['name'],
                    'head_name' => 'To be assigned',
                    'head_contact' => '',
                    'color' => $department['color'],
                    'is_active' => true,
                ]
            );
        }
    }
}
