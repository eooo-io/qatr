<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReleaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version' => fake()->numerify('#.#.#'),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'release_date' => fake()->optional()->date(),
            'status' => fake()->randomElement(['planning', 'in_progress', 'released']),
            'project_id' => Project::factory(),
            'created_by' => User::factory(),
        ];
    }
}
