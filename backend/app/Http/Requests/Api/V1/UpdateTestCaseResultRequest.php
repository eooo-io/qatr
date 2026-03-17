<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTestCaseResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['passed', 'failed', 'blocked', 'skipped', 'in_progress'])],
            'actual_result' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'attachments' => ['nullable', 'array'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
