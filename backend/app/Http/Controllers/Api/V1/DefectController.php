<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreDefectRequest;
use App\Http\Requests\Api\V1\UpdateDefectRequest;
use App\Http\Resources\Api\V1\DefectResource;
use App\Models\Defect;
use App\Models\TestCaseResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DefectController
{
    public function index(TestCaseResult $testCaseResult): AnonymousResourceCollection
    {
        return DefectResource::collection($testCaseResult->defects);
    }

    public function store(StoreDefectRequest $request, TestCaseResult $testCaseResult): JsonResponse
    {
        $defect = $testCaseResult->defects()->create([
            ...$request->validated(),
            'reported_by' => $request->user()->id,
        ]);

        return (new DefectResource($defect->refresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TestCaseResult $testCaseResult, Defect $defect): DefectResource
    {
        return new DefectResource($defect->load('result'));
    }

    public function update(UpdateDefectRequest $request, TestCaseResult $testCaseResult, Defect $defect): DefectResource
    {
        $defect->update($request->validated());

        return new DefectResource($defect);
    }

    public function destroy(TestCaseResult $testCaseResult, Defect $defect): JsonResponse
    {
        $defect->delete();

        return response()->json(null, 204);
    }
}
