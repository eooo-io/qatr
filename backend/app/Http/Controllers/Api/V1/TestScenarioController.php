<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTestScenarioRequest;
use App\Http\Requests\Api\V1\UpdateTestScenarioRequest;
use App\Http\Resources\Api\V1\TestScenarioResource;
use App\Models\TestPlan;
use App\Models\TestScenario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestScenarioController
{
    public function index(Request $request, TestPlan $testPlan): AnonymousResourceCollection
    {
        $scenarios = $testPlan->scenarios()
            ->withCount('testCases')
            ->when($request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->paginate($request->per_page ?? 25);

        return TestScenarioResource::collection($scenarios);
    }

    public function store(StoreTestScenarioRequest $request, TestPlan $testPlan): JsonResponse
    {
        $scenario = $testPlan->scenarios()->create($request->validated());

        return (new TestScenarioResource($scenario))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TestPlan $testPlan, TestScenario $scenario): TestScenarioResource
    {
        return new TestScenarioResource(
            $scenario->load('testCases.tags')->loadCount('testCases')
        );
    }

    public function update(UpdateTestScenarioRequest $request, TestPlan $testPlan, TestScenario $scenario): TestScenarioResource
    {
        $scenario->update($request->validated());

        return new TestScenarioResource($scenario);
    }

    public function destroy(TestPlan $testPlan, TestScenario $scenario): JsonResponse
    {
        $scenario->delete();

        return response()->json(null, 204);
    }
}
