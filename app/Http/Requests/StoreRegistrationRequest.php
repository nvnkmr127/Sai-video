<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|min:2|max:100',
            'phone'     => ['required', 'string', 'regex:/^\+?[0-9\s\-]{7,20}$/'],
            'otp'       => 'required|digits:6',
            'address'   => 'required|string|min:10|max:500',
            'workshop_id' => 'required|exists:workshops,id',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'full_name.min'    => 'Please provide your full name (at least 2 characters).',
            'phone.regex'      => 'Please enter a valid WhatsApp number (7–20 digits).',
            'otp.digits'       => 'OTP must be exactly 6 digits.',
            'address.min'      => 'Please enter your full mailing address (at least 10 characters).',
        ];
    }
}
