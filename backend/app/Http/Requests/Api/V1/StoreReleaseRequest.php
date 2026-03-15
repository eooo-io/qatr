<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+\.\d+\.\d+(-[a-zA-Z0-9.]+)?(\+[a-zA-Z0-9.]+)?$/',
                Rule::unique('releases')->where('project_id', $this->route('project')->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'release_date' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::in(['planning', 'in_progress', 'released'])],
        ];
    }

    public function messages(): array
    {
        return [
            'version.regex' => 'Version must follow SemVer format (e.g. 1.0.0, 1.2.3-beta).',
        ];
    }
}
