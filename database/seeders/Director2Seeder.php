<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Director2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'aggrey.busolo@ksg.ac.ke'],
            [
                'name' => 'Aggrey Busolo',
                'email' => 'aggrey.busolo@ksg.ac.ke',
                'password' => Hash::make('Pozee@5268'),
                'role' => UserRole::Director,
                'department_id' => null, // Directors typically don't have departments
            ]
        );

        $this->command->info('✓ Director 2 (Aggrey Busolo) created successfully');
    }
}
