<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator The validator instance containing validation errors.
     * @throws HttpResponseException Thrown when validation fails, redirecting back with error messages.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            redirect()->back()->with([
                'message' => [
                    'message' => $validator->errors()->first(),
                    'type' => 'danger'
                ],
            ])
        );
    }
}