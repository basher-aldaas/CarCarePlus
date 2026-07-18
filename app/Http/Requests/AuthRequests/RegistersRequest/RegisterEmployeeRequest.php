<?php

namespace App\Http\Requests\AuthRequests\RegistersRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'phone'     => ['required', 'string', 'unique:users,phone'],
            'password'  => ['required', 'string', 'min:8'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'type'      => ['required', 'string', 'in:washer,mechanic,admin'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
