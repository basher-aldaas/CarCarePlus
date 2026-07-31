<?php

namespace App\Http\Requests\OperationsRequest\BranchRequest;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateBranchRequest extends FormRequest
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
            'admin_id' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if (! User::role('admin')->whereKey($value)->exists()) {
                        $fail('The selected admin must be a user with the admin role.');
                    }
                },
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'name_ar' => [
                'required',
                'string',
                'max:255'
            ],

            'city' => [
                'required',
                'string',
                'max:255'
            ],

            'address' => [
                'required',
                'string'
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90'
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180'
            ],

            'phone' => [
                'required',
                'string',
                'max:30'
            ],

            'is_active' => [
                'nullable',
                'boolean'
            ],

            'working_hours' => [
                'nullable',
                'array'
            ],

            'is_24h' => [
                'nullable',
                'boolean'
            ],
        ];
    }
}
