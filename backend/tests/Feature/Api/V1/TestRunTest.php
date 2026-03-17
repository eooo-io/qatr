<?php

use App\Models\Project;
use App\Models\Release;
use App\Models\TestCase;
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
    $this->project->testPlans()->attach($this->plan);
    $this->release = Release::factory()->create([
        'project_id' => $this->project->id,
        'created_by' => $this->user->id,
        'version' => '1.0.0',
    ]);
});

describe('index', function () {
    it('lists test runs for a release', function () {
        TestRun::factory(2)->create([
            'release_id' => $this->release->id,
            'test_plan_id' => $this->plan->id,
            'executor_id' => $this->user->id,
        ]);

        $this->getJson("/api/v1/releases/{$this->release->id}/runs")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('filters by status', function () {
        TestRun::factory()->create([
            'release_id' => $this->release->id,
            'test_plan_id' => $this->plan->id,
            'executor_id' => $this->user->id,
            'status' => 'in_progress',
        ]);
        TestRun::factory()->create([
            'release_id' => $this->release->id,
            'test_plan_id' => $this->plan->id,
            'executor_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $this->getJson("/api/v1/releases/{$this->release->id}/runs?status=in_progress")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

describe('store', function () {
    it('creates a test run and auto-generates pending results', function () {
        $scenario = TestScenario::factory()->create(['test_plan_id' => $this->plan->id]);
        $cases = TestCase::factory(3)->create(['test_scenario_id' => $scenario->id]);

        $response = $this->postJson("/api/v1/releases/{$this->release->id}/runs", [
            'test_plan_id' => $this->plan->id,
            'environment' => ['browser' => 'chrome', 'os' => 'linux'],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.results_count', 3);

        // Verify all results are pending
        $runId = $response->json('data.id');
        expect(\App\Models\TestCaseResult::where('test_run_id', $runId)->where('status', 'pending')->count())->toBe(3);
    });

    it('starts the run immediately with started_at', function () {
        $response = $this->postJson("/api/v1/releases/{$this->release->id}/runs", [
            'test_plan_id' => $this->plan->id,
        ]);

        $response->assertStatus(201);
        expect($response->json('data.started_at'))->not->toBeNull();
    });
});

describe('show', function () {
    it('returns a run with results and test cases', function () {
        $scenario = TestScenario::factory()->create(['test_plan_id' => $this->plan->id]);
        $case = TestCase::factory()->create(['test_scenario_id' => $scenario->id]);

        $run = TestRun::factory()->create([
            'release_id' => $this->release->id,
            'test_plan_id' => $this->plan->id,
            'executor_id' => $this->user->id,
        ]);
        $run->results()->create([
            'test_case_id' => $case->id,
            'status' => 'pending',
        ]);

        $this->getJson("/api/v1/releases/{$this->release->id}/runs/{$run->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $run->id)
            ->assertJsonStructure(['data' => ['results', 'test_plan']]);
    });
});

describe('complete', function () {
    it('marks a run as completed', function () {
        $run = TestRun::factory()->create([
            'release_id' => $this->release->id,
            'test_plan_id' => $this->plan->id,
            'executor_id' => $this->user->id,
            'status' => 'in_progress',
        ]);

        $this->postJson("/api/v1/runs/{$run->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $run->refresh();
        expect($run->completed_at)->not->toBeNull();
    });
});

describe('progress', function () {
    it('returns progress counts', function () {
        $scenario = TestScenario::factory()->create(['test_plan_id' => $this->plan->id]);
        $cases = TestCase::factory(4)->create(['test_scenario_id' => $scenario->id]);

        $run = TestRun::factory()->create([
            'release_id' => $this->release->id,
            'test_plan_id' => $this->plan->id,
            'executor_id' => $this->user->id,
        ]);

        $run->results()->createMany([
            ['test_case_id' => $cases[0]->id, 'status' => 'passed'],
            ['test_case_id' => $cases[1]->id, 'status' => 'failed'],
            ['test_case_id' => $cases[2]->id, 'status' => 'pending'],
            ['test_case_id' => $cases[3]->id, 'status' => 'skipped'],
        ]);

        $this->getJson("/api/v1/runs/{$run->id}/progress")
            ->assertOk()
            ->assertJsonPath('total', 4)
            ->assertJsonPath('passed', 1)
            ->assertJsonPath('failed', 1)
            ->assertJsonPath('pending', 1)
            ->assertJsonPath('skipped', 1);
    });
});
