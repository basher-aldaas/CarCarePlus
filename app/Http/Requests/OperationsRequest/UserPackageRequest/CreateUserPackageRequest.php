<?php

namespace App\Http\Requests\OperationsRequest\UserPackageRequest;

use App\Enums\UserPackageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'user_id' => [
                'required',
                'exists:users,id'
            ],

            'package_id' => [
                'required',
                'exists:packages,id'
            ],

            'start_date' => [
                'required',
                'date'
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date'
            ],

            'remaining_count' => [
                'required',
                'integer',
                'min:0'
            ],

            'status' => [
                'sometimes',
                Rule::in(UserPackageStatus::values())
            ],

        ];
    }
}