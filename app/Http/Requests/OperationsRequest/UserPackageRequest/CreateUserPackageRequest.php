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

            'package_id' => [
                'required',
                'exists:packages,id'
            ],

            'status' => [
                'sometimes',
                Rule::in(UserPackageStatus::values())
            ],

        ];
    }
}