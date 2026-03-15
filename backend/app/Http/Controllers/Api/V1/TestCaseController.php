<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTestCaseRequest;
use App\Http\Requests\Api\V1\UpdateTestCaseRequest;
use App\Http\Resources\Api\V1\TestCaseResource;
use App\Models\TestCase;
use App\Models\TestScenario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestCaseController
{
    public function index(Request $request, TestScenario $scenario): AnonymousResourceCollection
    {
        $cases = $scenario->testCases()
            ->with('tags')
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->automation_status, fn ($q, $s) => $q->where('automation_status', $s))
            ->when($request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->paginate($request->per_page ?? 25);

        return TestCaseResource::collection($cases);
    }

    public function store(StoreTestCaseRequest $request, TestScenario $scenario): JsonResponse
    {
        $data = $request->safe()->except('tag_ids');
        $testCase = $scenario->testCases()->create($data);

        if ($request->has('tag_ids')) {
            $testCase->tags()->sync($request->tag_ids);
        }

        return (new TestCaseResource($testCase->load('tags')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TestScenario $scenario, TestCase $testCase): TestCaseResource
    {
        return new TestCaseResource($testCase->load('tags'));
    }

    public function update(UpdateTestCaseRequest $request, TestScenario $scenario, TestCase $testCase): TestCaseResource
    {
        $data = $request->safe()->except('tag_ids');
        $testCase->update($data);

        if ($request->has('tag_ids')) {
            $testCase->tags()->sync($request->tag_ids);
        }

        return new TestCaseResource($testCase->load('tags'));
    }

    public function destroy(TestScenario $scenario, TestCase $testCase): JsonResponse
    {
        $testCase->delete();

        return response()->json(null, 204);
    }
}
