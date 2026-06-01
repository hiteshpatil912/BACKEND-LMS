<?php

namespace App\Http\Requests;

class UploadVideoRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'video' => 'required|mimes:mp4,mov,avi|max:51200',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
