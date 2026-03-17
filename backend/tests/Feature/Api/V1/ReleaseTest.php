<?php

use App\Models\Project;
use App\Models\Release;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
});

describe('index', function () {
    it('lists releases for a project', function () {
        Release::factory(3)->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
        ]);

        $this->getJson("/api/v1/projects/{$this->project->id}/releases")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('filters by status', function () {
        Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'status' => 'planning',
            'version' => '1.0.0',
        ]);
        Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'status' => 'released',
            'version' => '0.9.0',
        ]);

        $this->getJson("/api/v1/projects/{$this->project->id}/releases?status=planning")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'planning');
    });

    it('searches by name and version', function () {
        Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'name' => 'Alpha Release',
            'version' => '1.0.0',
        ]);
        Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'name' => 'Beta Release',
            'version' => '2.0.0',
        ]);

        $this->getJson("/api/v1/projects/{$this->project->id}/releases?search=Alpha")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

describe('store', function () {
    it('creates a release with SemVer version', function () {
        $response = $this->postJson("/api/v1/projects/{$this->project->id}/releases", [
            'version' => '1.0.0',
            'name' => 'First Release',
            'description' => 'Initial release',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.version', '1.0.0')
            ->assertJsonPath('data.name', 'First Release');
    });

    it('rejects invalid SemVer', function () {
        $this->postJson("/api/v1/projects/{$this->project->id}/releases", [
            'version' => 'not-semver',
            'name' => 'Bad Release',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('version');
    });

    it('rejects duplicate version in same project', function () {
        Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'version' => '1.0.0',
        ]);

        $this->postJson("/api/v1/projects/{$this->project->id}/releases", [
            'version' => '1.0.0',
            'name' => 'Duplicate',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('version');
    });

    it('allows same version in different projects', function () {
        Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'version' => '1.0.0',
        ]);

        $otherProject = Project::factory()->create(['owner_id' => $this->user->id]);

        $this->postJson("/api/v1/projects/{$otherProject->id}/releases", [
            'version' => '1.0.0',
            'name' => 'Same Version Different Project',
        ])->assertStatus(201);
    });
});

describe('show', function () {
    it('returns a release with test runs', function () {
        $release = Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'version' => '1.0.0',
        ]);

        $this->getJson("/api/v1/projects/{$this->project->id}/releases/{$release->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $release->id)
            ->assertJsonStructure(['data' => ['test_runs']]);
    });
});

describe('update', function () {
    it('updates a release', function () {
        $release = Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'version' => '1.0.0',
        ]);

        $this->putJson("/api/v1/projects/{$this->project->id}/releases/{$release->id}", [
            'name' => 'Updated Name',
            'status' => 'in_progress',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.status', 'in_progress');
    });
});

describe('destroy', function () {
    it('deletes a release', function () {
        $release = Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'version' => '1.0.0',
        ]);

        $this->deleteJson("/api/v1/projects/{$this->project->id}/releases/{$release->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('releases', ['id' => $release->id]);
    });
});

describe('suggestVersion', function () {
    it('suggests initial versions when no releases exist', function () {
        $this->getJson("/api/v1/projects/{$this->project->id}/releases/suggest-version")
            ->assertOk()
            ->assertJsonPath('latest', null)
            ->assertJsonPath('suggestions.patch', '0.0.1')
            ->assertJsonPath('suggestions.minor', '0.1.0')
            ->assertJsonPath('suggestions.major', '1.0.0');
    });

    it('suggests incremented versions based on latest', function () {
        Release::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
            'version' => '1.2.3',
        ]);

        $this->getJson("/api/v1/projects/{$this->project->id}/releases/suggest-version")
            ->assertOk()
            ->assertJsonPath('latest', '1.2.3')
            ->assertJsonPath('suggestions.patch', '1.2.4')
            ->assertJsonPath('suggestions.minor', '1.3.0')
            ->assertJsonPath('suggestions.major', '2.0.0');
    });
});
