<?php

namespace Database\Factories;

use App\Models\TestCase;
use App\Models\TestRun;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestCaseResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'test_run_id' => TestRun::factory(),
            'test_case_id' => TestCase::factory(),
            'status' => 'pending',
            'actual_result' => null,
            'notes' => null,
            'attachments' => null,
            'duration_seconds' => null,
            'executed_by' => null,
            'executed_at' => null,
        ];
    }
}
