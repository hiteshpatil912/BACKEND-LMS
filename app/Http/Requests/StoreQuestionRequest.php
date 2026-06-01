<?php

namespace App\Http\Requests;

class StoreQuestionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quiz_id' => 'required|exists:quizzes,id',
            'question' => 'required|string',
            'option_1' => 'required|string',
            'option_2' => 'required|string',
            'option_3' => 'required|string',
            'option_4' => 'required|string',
            'correct_answer' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
