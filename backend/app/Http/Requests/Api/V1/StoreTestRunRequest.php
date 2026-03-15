<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'test_plan_id' => ['required', 'integer', 'exists:test_plans,id'],
            'environment' => ['nullable', 'array'],
        ];
    }
}
