<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'role'              => UserRole::Employee,
            'department_id'     => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => UserRole::Admin, 'department_id' => null]);
    }

    public function director(): static
    {
        return $this->state(fn () => ['role' => UserRole::Director, 'department_id' => null]);
    }

    public function hod(int $departmentId): static
    {
        return $this->state(fn () => ['role' => UserRole::Hod, 'department_id' => $departmentId]);
    }

    public function employee(int $departmentId): static
    {
        return $this->state(fn () => ['role' => UserRole::Employee, 'department_id' => $departmentId]);
    }
}
