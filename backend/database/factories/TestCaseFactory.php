<?php

namespace Database\Factories;

use App\Models\TestScenario;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestCaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'steps' => [
                ['action' => 'Navigate to the page', 'expected' => 'Page loads successfully'],
                ['action' => 'Click the button', 'expected' => 'Action is triggered'],
            ],
            'expected_result' => fake()->sentence(),
            'priority' => fake()->randomElement(['critical', 'high', 'medium', 'low']),
            'type' => fake()->randomElement(['functional', 'smoke', 'integration', 'edge_case']),
            'automation_status' => 'manual',
            'sort_order' => 0,
            'test_scenario_id' => TestScenario::factory(),
        ];
    }
}
