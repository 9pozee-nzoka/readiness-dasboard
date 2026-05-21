<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (app()->isProduction() && (empty($email) || empty($password))) {
            throw new RuntimeException('ADMIN_EMAIL and ADMIN_PASSWORD must be set in production.');
        }

        if (empty($email) || empty($password)) {
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('admin.name'),
                'password' => Hash::make($password),
                'role' => UserRole::Admin,
                'department_id' => null,
                'is_approved' => true,
                'approved_at' => now(),
            ]
        );
    }
}
