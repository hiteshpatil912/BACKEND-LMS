<?php

namespace App\Http\Requests;

class StoreReviewRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
