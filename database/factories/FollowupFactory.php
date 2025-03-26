<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Followup>
 */
class FollowupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph,
            'type' => $this->faker->randomElement(['comment', 'solution']),
            'user_id' => User::factory(), // Assign a random user
            'created_at' => now(),
            'updated_at' => now(),
        ];

    }
}
