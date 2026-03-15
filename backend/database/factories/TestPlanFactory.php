<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['smoke', 'integration', 'feature', 'happy_path', 'edge_case']),
            'status' => fake()->randomElement(['draft', 'active', 'archived']),
            'created_by' => User::factory(),
        ];
    }
}
