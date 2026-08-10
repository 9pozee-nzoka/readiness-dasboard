<?php

namespace Database\Factories;

use App\Enums\DecisionOutcome;
use App\Enums\DeliberationStatus;
use App\Models\Deliberation;
use App\Models\Event;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deliberation>
 */
class DeliberationFactory extends Factory
{
    protected $model = Deliberation::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'requirement_id' => null,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'recommendation' => fake()->optional()->paragraph(),
            'status' => DeliberationStatus::Open,
            'outcome' => null,
            'decision_due_date' => fake()->optional(0.7, null)->dateTimeBetween('now', '+2 weeks')?->format('Y-m-d'),
            'decision_made' => false,
            'decision_notes' => null,
            'approved_by' => null,
            'approved_at' => null,
            'created_by' => User::factory(),
            'impact_level' => fake()->randomElement(['critical', 'medium', 'low']),
            'escalated_to' => null,
            'escalated_at' => null,
        ];
    }

    public function forRequirement(Requirement $requirement): static
    {
        return $this->state(fn () => [
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirement->id,
        ]);
    }

    public function closed(?DecisionOutcome $outcome = null): static
    {
        return $this->state(fn () => [
            'status' => DeliberationStatus::Closed,
            'outcome' => $outcome ?? fake()->randomElement(DecisionOutcome::cases()),
            'decision_made' => true,
            'decision_notes' => fake()->sentence(),
            'approved_at' => now(),
        ]);
    }

    public function escalated(): static
    {
        return $this->state(fn () => [
            'status' => DeliberationStatus::Escalated,
            'escalated_at' => now(),
        ]);
    }
}
