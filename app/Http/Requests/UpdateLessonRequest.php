<?php

namespace App\Http\Requests;

class UpdateLessonRequest extends ApiFormRequest
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
            'video_url' => 'nullable|url',
            'lesson_order' => 'required|integer',
            'is_preview' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
