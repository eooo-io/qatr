<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'scenarios' => TestScenarioResource::collection($this->whenLoaded('scenarios')),
            'scenarios_count' => $this->whenCounted('scenarios'),
            'test_cases_count' => $this->whenCounted('testCases'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
