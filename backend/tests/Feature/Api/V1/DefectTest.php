<?php

use App\Models\Defect;
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
        'status' => 'failed',
    ]);
});

describe('index', function () {
    it('lists defects for a result', function () {
        Defect::factory(2)->create([
            'test_case_result_id' => $this->result->id,
            'reported_by' => $this->user->id,
        ]);

        $this->getJson("/api/v1/results/{$this->result->id}/defects")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });
});

describe('store', function () {
    it('creates a defect for a failed result', function () {
        $response = $this->postJson("/api/v1/results/{$this->result->id}/defects", [
            'title' => 'Login button broken',
            'description' => 'Button does not respond to clicks',
            'severity' => 'high',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Login button broken')
            ->assertJsonPath('data.severity', 'high')
            ->assertJsonPath('data.status', 'open');
    });

    it('creates a defect with external tracker URL', function () {
        $this->postJson("/api/v1/results/{$this->result->id}/defects", [
            'title' => 'UI glitch',
            'severity' => 'low',
            'external_tracker_url' => 'https://github.com/org/repo/issues/42',
        ])->assertStatus(201)
            ->assertJsonPath('data.external_tracker_url', 'https://github.com/org/repo/issues/42');
    });

    it('rejects invalid severity', function () {
        $this->postJson("/api/v1/results/{$this->result->id}/defects", [
            'title' => 'Bug',
            'severity' => 'invalid',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('severity');
    });
});

describe('update', function () {
    it('updates a defect status', function () {
        $defect = Defect::factory()->create([
            'test_case_result_id' => $this->result->id,
            'reported_by' => $this->user->id,
        ]);

        $this->putJson("/api/v1/results/{$this->result->id}/defects/{$defect->id}", [
            'status' => 'resolved',
        ])->assertOk()
            ->assertJsonPath('data.status', 'resolved');
    });
});

describe('destroy', function () {
    it('deletes a defect', function () {
        $defect = Defect::factory()->create([
            'test_case_result_id' => $this->result->id,
            'reported_by' => $this->user->id,
        ]);

        $this->deleteJson("/api/v1/results/{$this->result->id}/defects/{$defect->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('defects', ['id' => $defect->id]);
    });
});
