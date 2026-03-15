<?php

namespace Database\Factories;

use App\Models\TestCaseResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DefectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'test_case_result_id' => TestCaseResult::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(['critical', 'high', 'medium', 'low']),
            'status' => 'open',
            'external_tracker_url' => fake()->optional()->url(),
            'reported_by' => User::factory(),
        ];
    }
}
