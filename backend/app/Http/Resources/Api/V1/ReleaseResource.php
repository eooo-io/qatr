<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReleaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'name' => $this->name,
            'description' => $this->description,
            'release_date' => $this->release_date?->toDateString(),
            'status' => $this->status,
            'project_id' => $this->project_id,
            'created_by' => $this->created_by,
            'test_runs_count' => $this->whenCounted('testRuns'),
            'test_runs' => TestRunResource::collection($this->whenLoaded('testRuns')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
