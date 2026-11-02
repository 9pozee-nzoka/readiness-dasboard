<?php

namespace Database\Seeders;

use App\Models\Hostel;
use Illuminate\Database\Seeder;

class HostelSeeder extends Seeder
{
    public function run(): void
    {
        $hostels = [
            ['name' => 'M.Kobia', 'residency_type' => 'Executive', 'capacity' => 80, 'display_order' => 1],
            ['name' => 'Gateere', 'residency_type' => 'Deluxe', 'capacity' => 56, 'display_order' => 2],
            ['name' => 'Wamalwa', 'residency_type' => 'Deluxe', 'capacity' => 54, 'display_order' => 3],
            ['name' => 'Sawe', 'residency_type' => 'Deluxe', 'capacity' => 54, 'display_order' => 4],
            ['name' => 'Mekatilili', 'residency_type' => 'Deluxe', 'capacity' => 50, 'display_order' => 5, 'is_active' => false, 'notes' => 'Under renovations'],
        ];

        foreach ($hostels as $hostel) {
            Hostel::create($hostel);
        }
    }
}
