<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DefectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_case_result_id' => $this->test_case_result_id,
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity,
            'status' => $this->status,
            'external_tracker_url' => $this->external_tracker_url,
            'reported_by' => $this->reported_by,
            'result' => new TestCaseResultResource($this->whenLoaded('result')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
