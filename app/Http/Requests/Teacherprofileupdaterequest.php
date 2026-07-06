<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TeacherProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'           => ['sometimes', 'email', 'max:255'],
            'mobile_number'   => ['sometimes', 'string', 'max:15'],
            'current_password'=> ['required_with:new_password', 'string'],
            'new_password'    => ['sometimes', 'string', 'min:6', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'                    => 'Please provide a valid email address.',
            'mobile_number.max'              => 'Mobile number must not exceed 15 characters.',
            'current_password.required_with' => 'Current password is required to set a new password.',
            'new_password.min'               => 'New password must be at least 6 characters.',
            'new_password.confirmed'         => 'New password confirmation does not match.',
        ];
    }

    // Return JSON error instead of redirect on validation failure
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}