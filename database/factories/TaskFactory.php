<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'title'   => fake()->sentence(3),
            'is_done' => fake()->boolean(30),
            'due_at'  => fake()->optional()->dateTimeBetween('now', '+14 days'),
        ];
    }
}
