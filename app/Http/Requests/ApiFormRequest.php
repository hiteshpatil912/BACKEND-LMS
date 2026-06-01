<?php

namespace App\Http\Requests;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    use ApiResponse;

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->errorResponse('Validation failed', 422, $validator->errors())
        );
    }
}
