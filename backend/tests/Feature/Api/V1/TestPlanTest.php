<?php

use App\Models\Project;
use App\Models\TestPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
});

describe('index', function () {
    it('lists test plans for a project', function () {
        $plans = TestPlan::factory(3)->create(['created_by' => $this->user->id]);
        $plans->each(fn ($plan) => $this->project->testPlans()->attach($plan));

        $this->getJson("/api/v1/projects/{$this->project->id}/test-plans")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('filters by type', function () {
        $smoke = TestPlan::factory()->create(['type' => 'smoke', 'created_by' => $this->user->id]);
        $feature = TestPlan::factory()->create(['type' => 'feature', 'created_by' => $this->user->id]);
        $this->project->testPlans()->attach([$smoke->id, $feature->id]);

        $this->getJson("/api/v1/projects/{$this->project->id}/test-plans?type=smoke")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'smoke');
    });
});

describe('store', function () {
    it('creates a test plan and attaches to project', function () {
        $response = $this->postJson("/api/v1/projects/{$this->project->id}/test-plans", [
            'title' => 'Login Smoke Tests',
            'description' => 'Basic login flow tests',
            'type' => 'smoke',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Login Smoke Tests');

        // Verify it's attached to the project
        expect($this->project->testPlans()->count())->toBe(1);
    });
});

describe('show', function () {
    it('returns a test plan with scenarios and cases', function () {
        $plan = TestPlan::factory()->create(['created_by' => $this->user->id]);
        $this->project->testPlans()->attach($plan);

        $this->getJson("/api/v1/projects/{$this->project->id}/test-plans/{$plan->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $plan->id)
            ->assertJsonStructure(['data' => ['scenarios']]);
    });
});

describe('update', function () {
    it('updates a test plan', function () {
        $plan = TestPlan::factory()->create(['created_by' => $this->user->id]);
        $this->project->testPlans()->attach($plan);

        $this->putJson("/api/v1/projects/{$this->project->id}/test-plans/{$plan->id}", [
            'title' => 'Updated Title',
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.status', 'active');
    });
});

describe('destroy', function () {
    it('detaches from project and deletes orphaned plan', function () {
        $plan = TestPlan::factory()->create(['created_by' => $this->user->id]);
        $this->project->testPlans()->attach($plan);

        $this->deleteJson("/api/v1/projects/{$this->project->id}/test-plans/{$plan->id}")
            ->assertStatus(204);

        expect($this->project->testPlans()->count())->toBe(0);
        $this->assertDatabaseMissing('test_plans', ['id' => $plan->id]);
    });

    it('keeps plan if attached to other projects', function () {
        $plan = TestPlan::factory()->create(['created_by' => $this->user->id]);
        $otherProject = Project::factory()->create(['owner_id' => $this->user->id]);

        $this->project->testPlans()->attach($plan);
        $otherProject->testPlans()->attach($plan);

        $this->deleteJson("/api/v1/projects/{$this->project->id}/test-plans/{$plan->id}")
            ->assertStatus(204);

        // Plan still exists because it's attached to otherProject
        $this->assertDatabaseHas('test_plans', ['id' => $plan->id]);
    });
});

describe('attach', function () {
    it('attaches an existing plan to a project', function () {
        $plan = TestPlan::factory()->create(['created_by' => $this->user->id]);

        $this->postJson("/api/v1/projects/{$this->project->id}/test-plans/attach", [
            'test_plan_id' => $plan->id,
        ])->assertOk();

        expect($this->project->testPlans()->count())->toBe(1);
    });
});
