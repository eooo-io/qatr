<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTestPlanRequest;
use App\Http\Requests\Api\V1\UpdateTestPlanRequest;
use App\Http\Resources\Api\V1\TestPlanResource;
use App\Models\Project;
use App\Models\TestPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestPlanController
{
    /**
     * List test plans for a project.
     */
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $plans = $project->testPlans()
            ->withCount(['scenarios', 'testCases'])
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return TestPlanResource::collection($plans);
    }

    /**
     * Create a test plan and attach it to the project.
     */
    public function store(StoreTestPlanRequest $request, Project $project): JsonResponse
    {
        $plan = TestPlan::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $project->testPlans()->attach($plan->id);

        return (new TestPlanResource($plan->loadCount(['scenarios', 'testCases'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project, TestPlan $testPlan): TestPlanResource
    {
        return new TestPlanResource(
            $testPlan->load('scenarios.testCases.tags')
                ->loadCount(['scenarios', 'testCases'])
        );
    }

    public function update(UpdateTestPlanRequest $request, Project $project, TestPlan $testPlan): TestPlanResource
    {
        $testPlan->update($request->validated());

        return new TestPlanResource($testPlan);
    }

    /**
     * Detach a test plan from the project. If orphaned, delete it entirely.
     */
    public function destroy(Project $project, TestPlan $testPlan): JsonResponse
    {
        $project->testPlans()->detach($testPlan->id);

        // If the plan is no longer attached to any projects, delete it
        if ($testPlan->projects()->count() === 0) {
            $testPlan->delete();
        }

        return response()->json(null, 204);
    }

    /**
     * Attach an existing test plan to a project.
     */
    public function attach(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'test_plan_id' => ['required', 'integer', 'exists:test_plans,id'],
        ]);

        $project->testPlans()->syncWithoutDetaching([$request->test_plan_id]);

        return response()->json(['message' => 'Test plan attached to project.']);
    }

    /**
     * Detach a test plan from a project without deleting it.
     */
    public function detach(Project $project, TestPlan $testPlan): JsonResponse
    {
        $project->testPlans()->detach($testPlan->id);

        return response()->json(null, 204);
    }
}
