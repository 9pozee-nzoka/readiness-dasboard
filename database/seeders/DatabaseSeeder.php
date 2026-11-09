<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // In production, only seed the admin user
        if (app()->environment('production')) {
            $this->call([
                AdminUserSeeder::class,
            ]);

            return;
        }

        // In development/local, seed all data including demos
        $this->call([
            DepartmentSeeder::class,
            EventTypeRequirementSeeder::class,
            AdminUserSeeder::class,
            DemoDataSeeder::class,
            RequirementSeeder::class,
            October2026DataSeeder::class,
        ]);
    }
}
