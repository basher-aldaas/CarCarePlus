<?php

namespace App\Http\Requests\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Validates against the currently authenticated (sanctum) user's password.
            'current_password' => ['required', 'string', 'current_password:sanctum'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }
}
