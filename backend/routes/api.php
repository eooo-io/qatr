<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DefectController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ReleaseController;
use App\Http\Controllers\Api\V1\TestCaseController;
use App\Http\Controllers\Api\V1\TestCaseResultController;
use App\Http\Controllers\Api\V1\TestPlanController;
use App\Http\Controllers\Api\V1\TestRunController;
use App\Http\Controllers\Api\V1\TestScenarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);

    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn (Request $request) => $request->user());

        // Projects
        Route::apiResource('projects', ProjectController::class);

        // Test Plans (scoped to project)
        Route::apiResource('projects.test-plans', TestPlanController::class)
            ->parameters(['test-plans' => 'testPlan']);
        Route::post('projects/{project}/test-plans/attach', [TestPlanController::class, 'attach']);
        Route::delete('projects/{project}/test-plans/{testPlan}/detach', [TestPlanController::class, 'detach']);

        // Test Scenarios (scoped to test plan)
        Route::apiResource('test-plans.scenarios', TestScenarioController::class)
            ->parameters(['test-plans' => 'testPlan', 'scenarios' => 'scenario']);

        // Test Cases (scoped to scenario)
        Route::apiResource('scenarios.test-cases', TestCaseController::class)
            ->parameters(['scenarios' => 'scenario', 'test-cases' => 'testCase']);

        // Releases (scoped to project)
        Route::get('projects/{project}/releases/suggest-version', [ReleaseController::class, 'suggestVersion']);
        Route::apiResource('projects.releases', ReleaseController::class)
            ->parameters(['releases' => 'release']);

        // Test Runs (scoped to release)
        Route::apiResource('releases.runs', TestRunController::class)
            ->parameters(['releases' => 'release', 'runs' => 'testRun'])
            ->only(['index', 'store', 'show']);
        Route::post('runs/{testRun}/complete', [TestRunController::class, 'complete']);
        Route::get('runs/{testRun}/progress', [TestRunController::class, 'progress']);

        // Test Case Results (scoped to run)
        Route::apiResource('runs.results', TestCaseResultController::class)
            ->parameters(['runs' => 'testRun', 'results' => 'testCaseResult'])
            ->only(['index', 'show', 'update']);

        // Result history for a test case
        Route::get('test-cases/{testCase}/result-history', [TestCaseResultController::class, 'history']);

        // Defects (scoped to result)
        Route::apiResource('results.defects', DefectController::class)
            ->parameters(['results' => 'testCaseResult', 'defects' => 'defect']);
    });
});
