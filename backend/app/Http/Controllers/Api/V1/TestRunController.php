<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTestRunRequest;
use App\Http\Resources\Api\V1\TestRunResource;
use App\Models\Release;
use App\Models\TestRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestRunController
{
    public function index(Request $request, Release $release): AnonymousResourceCollection
    {
        $runs = $release->testRuns()
            ->with('testPlan')
            ->withCount('results')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return TestRunResource::collection($runs);
    }

    /**
     * Create a test run and auto-generate pending results for all test cases in the plan.
     */
    public function store(StoreTestRunRequest $request, Release $release): JsonResponse
    {
        $run = TestRun::create([
            ...$request->validated(),
            'release_id' => $release->id,
            'executor_id' => $request->user()->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Auto-generate pending TestCaseResult for every test case in the plan
        $testCaseIds = $run->testPlan->testCases()->pluck('test_cases.id');

        $results = $testCaseIds->map(fn ($id) => [
            'test_run_id' => $run->id,
            'test_case_id' => $id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        $run->results()->insert($results);

        return (new TestRunResource($run->load('testPlan')->loadCount('results')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Release $release, TestRun $testRun): TestRunResource
    {
        return new TestRunResource(
            $testRun->load(['testPlan', 'results.testCase', 'results.defects'])
                ->loadCount('results')
        );
    }

    /**
     * Complete a test run.
     */
    public function complete(TestRun $testRun): TestRunResource
    {
        $testRun->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return new TestRunResource($testRun->loadCount('results'));
    }

    /**
     * Get real-time progress for a test run.
     */
    public function progress(TestRun $testRun): JsonResponse
    {
        $counts = $testRun->results()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total = array_sum($counts);

        return response()->json([
            'total' => $total,
            'passed' => $counts['passed'] ?? 0,
            'failed' => $counts['failed'] ?? 0,
            'blocked' => $counts['blocked'] ?? 0,
            'skipped' => $counts['skipped'] ?? 0,
            'in_progress' => $counts['in_progress'] ?? 0,
            'pending' => $counts['pending'] ?? 0,
            'completed_percentage' => $total > 0
                ? round((($total - ($counts['pending'] ?? 0) - ($counts['in_progress'] ?? 0)) / $total) * 100, 1)
                : 0,
        ]);
    }
}
