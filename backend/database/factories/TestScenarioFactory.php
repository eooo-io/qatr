<?php

namespace Database\Factories;

use App\Models\TestPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestScenarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'preconditions' => fake()->sentence(),
            'sort_order' => 0,
            'test_plan_id' => TestPlan::factory(),
        ];
    }
}
