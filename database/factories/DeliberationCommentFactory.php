<?php

namespace Database\Factories;

use App\Models\Deliberation;
use App\Models\DeliberationComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliberationComment>
 */
class DeliberationCommentFactory extends Factory
{
    protected $model = DeliberationComment::class;

    public function definition(): array
    {
        return [
            'deliberation_id' => Deliberation::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->sentence(),
        ];
    }
}
