<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDefectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'severity' => ['required', Rule::in(['critical', 'high', 'medium', 'low'])],
            'external_tracker_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
