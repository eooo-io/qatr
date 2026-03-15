<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TestCaseController;
use App\Http\Controllers\Api\V1\TestPlanController;
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
    });
});
