<?php

namespace App\Http\Requests;

class StoreDiscussionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'message' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
