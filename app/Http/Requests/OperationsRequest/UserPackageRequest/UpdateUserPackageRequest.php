<?php

namespace App\Http\Requests\OperationsRequest\UserPackageRequest;

use App\Enums\UserPackageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'user_id' => [
                'sometimes',
                'exists:users,id'
            ],

            'package_id' => [
                'sometimes',
                'exists:packages,id'
            ],

            'start_date' => [
                'sometimes',
                'date'
            ],

            'end_date' => [
                'sometimes',
                'date',
                'after_or_equal:start_date'
            ],

            'remaining_count' => [
                'sometimes',
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