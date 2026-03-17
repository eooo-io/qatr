<?php

namespace Database\Factories;

use App\Models\Release;
use App\Models\TestPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestRunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'test_plan_id' => TestPlan::factory(),
            'release_id' => Release::factory(),
            'executor_id' => User::factory(),
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
            'environment' => null,
        ];
    }
}
