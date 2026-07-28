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
