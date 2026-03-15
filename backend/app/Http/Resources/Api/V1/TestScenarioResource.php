<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestScenarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'preconditions' => $this->preconditions,
            'sort_order' => $this->sort_order,
            'test_plan_id' => $this->test_plan_id,
            'test_cases' => TestCaseResource::collection($this->whenLoaded('testCases')),
            'test_cases_count' => $this->whenCounted('testCases'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
