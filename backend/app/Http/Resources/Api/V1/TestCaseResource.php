<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestCaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'steps' => $this->steps,
            'expected_result' => $this->expected_result,
            'priority' => $this->priority,
            'type' => $this->type,
            'automation_status' => $this->automation_status,
            'automation_framework' => $this->automation_framework,
            'automation_script_path' => $this->automation_script_path,
            'sort_order' => $this->sort_order,
            'test_scenario_id' => $this->test_scenario_id,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
