<?php

namespace Database\Factories;

use App\Models\PlanningWeek;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'planning_week_id' => PlanningWeek::factory(),
            'name'             => fake()->randomElement([
                'Board Meeting', 'Staff Training', 'Annual General Meeting',
                'Budget Review', 'Stakeholder Forum', 'Graduation Ceremony',
                'Open Day', 'Audit Review', 'Leadership Retreat', 'Awards Night',
            ]).' '.fake()->year(),
            'type'             => fake()->randomElement([
                'Meeting', 'Training', 'Ceremony', 'Forum', 'Review', 'Retreat',
            ]),
            'event_date'       => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'event_time'       => fake()->time('H:i'),
            'venue'            => fake()->randomElement([
                'Main Boardroom', 'Conference Hall A', 'Auditorium', 'Training Room 1',
                'Executive Suite', 'Open Grounds',
            ]),
            'description'      => fake()->sentence(),
        ];
    }
}
