<?php

namespace App\Http\Requests\OperationsRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only an authenticated user may edit their own profile.
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->id())],
            'phone' => ['sometimes', 'string', 'max:20', Rule::unique('users', 'phone')->ignore(auth()->id())],
            'image_url' => ['sometimes','nullable', 'string','mimes:jpg,jpeg,png,webp', 'max:255'],
        ];
    }
}
