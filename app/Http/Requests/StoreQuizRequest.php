<?php

namespace App\Http\Requests;

class StoreQuizRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_marks' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
