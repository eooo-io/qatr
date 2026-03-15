<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('index', function () {
    it('lists projects owned by the user', function () {
        Project::factory(3)->create(['owner_id' => $this->user->id]);
        Project::factory(2)->create(); // other users

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('filters by search query', function () {
        Project::factory()->create(['name' => 'QATR App', 'owner_id' => $this->user->id]);
        Project::factory()->create(['name' => 'Other App', 'owner_id' => $this->user->id]);

        $this->getJson('/api/v1/projects?search=QATR')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

describe('store', function () {
    it('creates a project', function () {
        $response = $this->postJson('/api/v1/projects', [
            'name' => 'My Project',
            'description' => 'A test project',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'My Project');

        $this->assertDatabaseHas('projects', [
            'name' => 'My Project',
            'owner_id' => $this->user->id,
        ]);
    });

    it('validates required fields', function () {
        $this->postJson('/api/v1/projects', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    });
});

describe('show', function () {
    it('returns a project', function () {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $this->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $project->id);
    });
});

describe('update', function () {
    it('updates a project', function () {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $this->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Updated Name',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    });
});

describe('destroy', function () {
    it('deletes a project', function () {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $this->deleteJson("/api/v1/projects/{$project->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    });
});
