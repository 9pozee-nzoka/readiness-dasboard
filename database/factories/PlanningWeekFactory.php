<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanningWeek>
 */
class PlanningWeekFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-4 weeks', '+4 weeks');
        // Align to Monday
        $monday = (clone $start)->modify('monday this week');
        $friday = (clone $monday)->modify('+4 days');

        return [
            'label'      => 'Week of '.$monday->format('d M Y'),
            'week_start' => $monday->format('Y-m-d'),
            'week_end'   => $friday->format('Y-m-d'),
            'is_current' => false,
        ];
    }

    public function current(): static
    {
        return $this->state(['is_current' => true]);
    }
}
