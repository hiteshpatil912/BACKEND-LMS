<?php

namespace App\Http\Requests;

class StoreNotificationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
