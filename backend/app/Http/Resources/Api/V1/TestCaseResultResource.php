<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestCaseResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_run_id' => $this->test_run_id,
            'test_case_id' => $this->test_case_id,
            'status' => $this->status,
            'actual_result' => $this->actual_result,
            'notes' => $this->notes,
            'attachments' => $this->attachments,
            'duration_seconds' => $this->duration_seconds,
            'executed_by' => $this->executed_by,
            'executed_at' => $this->executed_at,
            'test_case' => new TestCaseResource($this->whenLoaded('testCase')),
            'defects' => DefectResource::collection($this->whenLoaded('defects')),
            'defects_count' => $this->whenCounted('defects'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
