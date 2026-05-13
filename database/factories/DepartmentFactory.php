<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'ICT', 'Finance', 'Administration', 'Security', 'Logistics',
            'Communications', 'Facilities', 'Human Resources', 'Protocol', 'Catering',
        ]);

        return [
            'name'         => $name,
            'slug'         => Str::slug($name),
            'head_name'    => fake()->name(),
            'head_contact' => fake()->phoneNumber(),
            'color'        => fake()->hexColor(),
            'is_active'    => true,
        ];
    }
}
