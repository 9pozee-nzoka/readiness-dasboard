<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requirement>
 */
class RequirementFactory extends Factory
{
    public function definition(): array
    {
        $isCompleted = fake()->boolean(60);

        return [
            'event_id'            => Event::factory(),
            'department_id'       => Department::factory(),
            'description'         => fake()->randomElement([
                'Prepare venue layout and seating arrangement',
                'Confirm attendance list and send invitations',
                'Set up audio-visual equipment',
                'Arrange catering and refreshments',
                'Prepare agenda and briefing documents',
                'Coordinate transport and parking',
                'Set up registration desk',
                'Prepare name tags and welcome packs',
                'Test internet and presentation equipment',
                'Brief security personnel on access control',
                'Arrange flowers and decorations',
                'Confirm MC and programme flow',
                'Prepare signage and directional boards',
                'Arrange photography and videography',
                'Confirm VIP protocol arrangements',
            ]),
            'is_completed'        => $isCompleted,
            'responsible_officer' => fake()->name(),
            'completed_at'        => $isCompleted ? fake()->dateTimeBetween('-1 week', 'now') : null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
}
