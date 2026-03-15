<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreReleaseRequest;
use App\Http\Requests\Api\V1\UpdateReleaseRequest;
use App\Http\Resources\Api\V1\ReleaseResource;
use App\Models\Project;
use App\Models\Release;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReleaseController
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $releases = $project->releases()
            ->withCount('testRuns')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%");
            }))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return ReleaseResource::collection($releases);
    }

    public function store(StoreReleaseRequest $request, Project $project): JsonResponse
    {
        $release = $project->releases()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return (new ReleaseResource($release->loadCount('testRuns')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project, Release $release): ReleaseResource
    {
        return new ReleaseResource(
            $release->load('testRuns.testPlan')
                ->loadCount('testRuns')
        );
    }

    public function update(UpdateReleaseRequest $request, Project $project, Release $release): ReleaseResource
    {
        $release->update($request->validated());

        return new ReleaseResource($release);
    }

    public function destroy(Project $project, Release $release): JsonResponse
    {
        $release->delete();

        return response()->json(null, 204);
    }

    /**
     * Suggest next SemVer versions based on the latest release.
     */
    public function suggestVersion(Project $project): JsonResponse
    {
        $latest = $project->releases()
            ->orderByDesc('created_at')
            ->first();

        if (! $latest) {
            return response()->json([
                'latest' => null,
                'suggestions' => [
                    'patch' => '0.0.1',
                    'minor' => '0.1.0',
                    'major' => '1.0.0',
                ],
            ]);
        }

        $parts = explode('.', explode('-', $latest->version)[0]);
        $major = (int) ($parts[0] ?? 0);
        $minor = (int) ($parts[1] ?? 0);
        $patch = (int) ($parts[2] ?? 0);

        return response()->json([
            'latest' => $latest->version,
            'suggestions' => [
                'patch' => "{$major}.{$minor}." . ($patch + 1),
                'minor' => "{$major}." . ($minor + 1) . '.0',
                'major' => ($major + 1) . '.0.0',
            ],
        ]);
    }
}
