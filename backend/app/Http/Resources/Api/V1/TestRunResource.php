<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_plan_id' => $this->test_plan_id,
            'release_id' => $this->release_id,
            'executor_id' => $this->executor_id,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'environment' => $this->environment,
            'test_plan' => new TestPlanResource($this->whenLoaded('testPlan')),
            'release' => new ReleaseResource($this->whenLoaded('release')),
            'results' => TestCaseResultResource::collection($this->whenLoaded('results')),
            'results_count' => $this->whenCounted('results'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
