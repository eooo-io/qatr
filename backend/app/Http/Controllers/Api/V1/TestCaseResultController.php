<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\UpdateTestCaseResultRequest;
use App\Http\Resources\Api\V1\TestCaseResultResource;
use App\Models\TestCase;
use App\Models\TestCaseResult;
use App\Models\TestRun;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestCaseResultController
{
    public function index(TestRun $testRun): AnonymousResourceCollection
    {
        $results = $testRun->results()
            ->with(['testCase', 'defects'])
            ->withCount('defects')
            ->orderBy('id')
            ->get();

        return TestCaseResultResource::collection($results);
    }

    public function show(TestRun $testRun, TestCaseResult $testCaseResult): TestCaseResultResource
    {
        return new TestCaseResultResource(
            $testCaseResult->load(['testCase.tags', 'defects'])
                ->loadCount('defects')
        );
    }

    /**
     * Record a test case result.
     */
    public function update(UpdateTestCaseResultRequest $request, TestRun $testRun, TestCaseResult $testCaseResult): TestCaseResultResource
    {
        $testCaseResult->update([
            ...$request->validated(),
            'executed_by' => $request->user()->id,
            'executed_at' => now(),
        ]);

        return new TestCaseResultResource($testCaseResult->load(['testCase', 'defects']));
    }

    /**
     * Get result history for a specific test case across all runs.
     */
    public function history(TestCase $testCase): AnonymousResourceCollection
    {
        $results = $testCase->results()
            ->with(['testRun.release'])
            ->whereNotNull('executed_at')
            ->orderByDesc('executed_at')
            ->paginate(20);

        return TestCaseResultResource::collection($results);
    }
}
