<?php

use App\Models\Project;
use App\Models\Release;
use App\Models\TestCase;
use App\Models\TestCaseResult;
use App\Models\TestPlan;
use App\Models\TestRun;
use App\Models\TestScenario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
    $this->plan = TestPlan::factory()->create(['created_by' => $this->user->id]);
    $this->scenario = TestScenario::factory()->create(['test_plan_id' => $this->plan->id]);
    $this->testCase = TestCase::factory()->create(['test_scenario_id' => $this->scenario->id]);
    $this->release = Release::factory()->create([
        'project_id' => $this->project->id,
        'created_by' => $this->user->id,
        'version' => '1.0.0',
    ]);
    $this->run = TestRun::factory()->create([
        'release_id' => $this->release->id,
        'test_plan_id' => $this->plan->id,
        'executor_id' => $this->user->id,
    ]);
    $this->result = TestCaseResult::factory()->create([
        'test_run_id' => $this->run->id,
        'test_case_id' => $this->testCase->id,
        'status' => 'pending',
    ]);
});

describe('index', function () {
    it('lists results for a test run', function () {
        $this->getJson("/api/v1/runs/{$this->run->id}/results")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending');
    });
});

describe('show', function () {
    it('returns a result with test case details', function () {
        $this->getJson("/api/v1/runs/{$this->run->id}/results/{$this->result->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $this->result->id)
            ->assertJsonStructure(['data' => ['test_case', 'defects']]);
    });
});

describe('update', function () {
    it('records a passed result', function () {
        $this->putJson("/api/v1/runs/{$this->run->id}/results/{$this->result->id}", [
            'status' => 'passed',
            'actual_result' => 'Login successful',
            'duration_seconds' => 45,
        ])->assertOk()
            ->assertJsonPath('data.status', 'passed')
            ->assertJsonPath('data.actual_result', 'Login successful')
            ->assertJsonPath('data.duration_seconds', 45);

        $this->result->refresh();
        expect($this->result->executed_by)->toBe($this->user->id);
        expect($this->result->executed_at)->not->toBeNull();
    });

    it('records a failed result with notes', function () {
        $this->putJson("/api/v1/runs/{$this->run->id}/results/{$this->result->id}", [
            'status' => 'failed',
            'actual_result' => 'Login button unresponsive',
            'notes' => 'Button click handler not firing',
        ])->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.notes', 'Button click handler not firing');
    });

    it('rejects invalid status', function () {
        $this->putJson("/api/v1/runs/{$this->run->id}/results/{$this->result->id}", [
            'status' => 'invalid',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('status');
    });
});

describe('history', function () {
    it('returns result history for a test case across runs', function () {
        // Create a second run with a result for the same test case
        $run2 = TestRun::factory()->create([
            'release_id' => $this->release->id,
            'test_plan_id' => $this->plan->id,
            'executor_id' => $this->user->id,
        ]);
        TestCaseResult::factory()->create([
            'test_run_id' => $run2->id,
            'test_case_id' => $this->testCase->id,
            'status' => 'passed',
            'executed_at' => now(),
        ]);

        // Update the first result to have an executed_at
        $this->result->update(['status' => 'failed', 'executed_at' => now()->subDay()]);

        $this->getJson("/api/v1/test-cases/{$this->testCase->id}/result-history")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });
});
