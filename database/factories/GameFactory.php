<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => $this->faker->randomElement(['netent','pragmatic']),
            'external_id' => $this->faker->unique()->uuid(),
            'title' => $this->faker->words(rand(2, 4), true),
            'category' => $this->faker->randomElement(['slots','live','table']),
            'is_active' => $this->faker->boolean(80),
            'rtp' => $this->faker->optional(0.7)->randomFloat(2, 90, 99.99),
        ];
    }
}
