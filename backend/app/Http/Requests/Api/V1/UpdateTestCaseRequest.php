<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTestCaseRequest extends FormRequest
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
            'steps' => ['sometimes', 'array', 'min:1'],
            'steps.*.action' => ['required_with:steps', 'string'],
            'steps.*.expected' => ['required_with:steps', 'string'],
            'expected_result' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', Rule::in(['critical', 'high', 'medium', 'low'])],
            'type' => ['sometimes', Rule::in(['functional', 'smoke', 'integration', 'edge_case'])],
            'automation_status' => ['sometimes', Rule::in(['manual', 'automated', 'pending'])],
            'automation_framework' => ['nullable', Rule::in(['cypress', 'selenium', 'pest', 'nightwatch'])],
            'automation_script_path' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
