<?php

namespace App\Http\Requests;

class UploadPdfRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pdf' => 'required|mimes:pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
