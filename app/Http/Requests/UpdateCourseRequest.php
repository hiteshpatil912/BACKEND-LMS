<?php

namespace App\Http\Requests;

class UpdateCourseRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'price' => 'required|numeric',
            'status' => 'required|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
