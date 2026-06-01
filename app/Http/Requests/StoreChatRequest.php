<?php

namespace App\Http\Requests;

class StoreChatRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
