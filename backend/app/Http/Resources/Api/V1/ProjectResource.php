<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'settings' => $this->settings,
            'owner_id' => $this->owner_id,
            'test_plans_count' => $this->whenCounted('testPlans'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
