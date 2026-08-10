<?php

namespace Database\Factories;

use App\Models\Deliberation;
use App\Models\DeliberationAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliberationAttachment>
 */
class DeliberationAttachmentFactory extends Factory
{
    protected $model = DeliberationAttachment::class;

    public function definition(): array
    {
        return [
            'deliberation_id' => Deliberation::factory(),
            'user_id' => User::factory(),
            'filename' => fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'path' => 'deliberations/'.fake()->randomNumber().'/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 10485760),
        ];
    }
}
