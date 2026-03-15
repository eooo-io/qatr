<?php

use App\Models\TestPlan;
use App\Models\TestScenario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->testPlan = TestPlan::factory()->create(['created_by' => $this->user->id]);
});

describe('index', function () {
    it('lists scenarios for a test plan', function () {
        TestScenario::factory(3)->create(['test_plan_id' => $this->testPlan->id]);

        $this->getJson("/api/v1/test-plans/{$this->testPlan->id}/scenarios")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });
});

describe('store', function () {
    it('creates a scenario', function () {
        $this->postJson("/api/v1/test-plans/{$this->testPlan->id}/scenarios", [
            'title' => 'Valid Login Flow',
            'description' => 'Tests for valid login scenarios',
            'preconditions' => 'User account exists',
        ])->assertStatus(201)
            ->assertJsonPath('data.title', 'Valid Login Flow');
    });
});

describe('show', function () {
    it('returns scenario with test cases', function () {
        $scenario = TestScenario::factory()->create(['test_plan_id' => $this->testPlan->id]);

        $this->getJson("/api/v1/test-plans/{$this->testPlan->id}/scenarios/{$scenario->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $scenario->id);
    });
});

describe('update', function () {
    it('updates a scenario', function () {
        $scenario = TestScenario::factory()->create(['test_plan_id' => $this->testPlan->id]);

        $this->putJson("/api/v1/test-plans/{$this->testPlan->id}/scenarios/{$scenario->id}", [
            'title' => 'Updated Scenario',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated Scenario');
    });
});

describe('destroy', function () {
    it('deletes a scenario', function () {
        $scenario = TestScenario::factory()->create(['test_plan_id' => $this->testPlan->id]);

        $this->deleteJson("/api/v1/test-plans/{$this->testPlan->id}/scenarios/{$scenario->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('test_scenarios', ['id' => $scenario->id]);
    });
});
