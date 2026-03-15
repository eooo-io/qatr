<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTestPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type' => ['sometimes', Rule::in(['smoke', 'integration', 'feature', 'happy_path', 'edge_case'])],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'archived'])],
        ];
    }
}
