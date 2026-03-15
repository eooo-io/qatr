<?php

use App\Models\Tag;
use App\Models\TestCase;
use App\Models\TestScenario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->scenario = TestScenario::factory()->create();
});

describe('index', function () {
    it('lists test cases for a scenario', function () {
        TestCase::factory(3)->create(['test_scenario_id' => $this->scenario->id]);

        $this->getJson("/api/v1/scenarios/{$this->scenario->id}/test-cases")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('filters by priority', function () {
        TestCase::factory()->create(['test_scenario_id' => $this->scenario->id, 'priority' => 'critical']);
        TestCase::factory()->create(['test_scenario_id' => $this->scenario->id, 'priority' => 'low']);

        $this->getJson("/api/v1/scenarios/{$this->scenario->id}/test-cases?priority=critical")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

describe('store', function () {
    it('creates a test case', function () {
        $this->postJson("/api/v1/scenarios/{$this->scenario->id}/test-cases", [
            'title' => 'Login with email',
            'steps' => [
                ['action' => 'Navigate to /login', 'expected' => 'Login form shown'],
                ['action' => 'Enter credentials', 'expected' => 'Fields accept input'],
            ],
            'priority' => 'critical',
        ])->assertStatus(201)
            ->assertJsonPath('data.title', 'Login with email')
            ->assertJsonPath('data.priority', 'critical');
    });

    it('creates a test case with tags', function () {
        $tag = Tag::factory()->create(['name' => 'auth']);

        $this->postJson("/api/v1/scenarios/{$this->scenario->id}/test-cases", [
            'title' => 'Tagged case',
            'steps' => [['action' => 'Do', 'expected' => 'Result']],
            'priority' => 'medium',
            'tag_ids' => [$tag->id],
        ])->assertStatus(201)
            ->assertJsonPath('data.tags.0.name', 'auth');
    });

    it('validates steps are required', function () {
        $this->postJson("/api/v1/scenarios/{$this->scenario->id}/test-cases", [
            'title' => 'No steps',
            'priority' => 'medium',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('steps');
    });
});

describe('show', function () {
    it('returns a test case with tags', function () {
        $testCase = TestCase::factory()->create(['test_scenario_id' => $this->scenario->id]);
        $tag = Tag::factory()->create();
        $testCase->tags()->attach($tag);

        $this->getJson("/api/v1/scenarios/{$this->scenario->id}/test-cases/{$testCase->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $testCase->id)
            ->assertJsonCount(1, 'data.tags');
    });
});

describe('update', function () {
    it('updates a test case', function () {
        $testCase = TestCase::factory()->create(['test_scenario_id' => $this->scenario->id]);

        $this->putJson("/api/v1/scenarios/{$this->scenario->id}/test-cases/{$testCase->id}", [
            'title' => 'Updated Case',
            'priority' => 'high',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated Case')
            ->assertJsonPath('data.priority', 'high');
    });

    it('syncs tags on update', function () {
        $testCase = TestCase::factory()->create(['test_scenario_id' => $this->scenario->id]);
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $testCase->tags()->attach($tag1);

        $this->putJson("/api/v1/scenarios/{$this->scenario->id}/test-cases/{$testCase->id}", [
            'tag_ids' => [$tag2->id],
        ])->assertOk();

        expect($testCase->fresh()->tags->pluck('id')->toArray())->toBe([$tag2->id]);
    });
});

describe('destroy', function () {
    it('deletes a test case', function () {
        $testCase = TestCase::factory()->create(['test_scenario_id' => $this->scenario->id]);

        $this->deleteJson("/api/v1/scenarios/{$this->scenario->id}/test-cases/{$testCase->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('test_cases', ['id' => $testCase->id]);
    });
});
