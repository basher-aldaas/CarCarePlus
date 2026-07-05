<?php

namespace App\Http\Requests\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'otp'      => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
