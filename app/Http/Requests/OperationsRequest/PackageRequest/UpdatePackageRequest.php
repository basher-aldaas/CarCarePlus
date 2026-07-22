<?php

namespace App\Http\Requests\OperationsRequest\PackageRequest;

use App\Enums\PackageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'type' => [
                'sometimes',
                Rule::in(PackageType::values())
            ],

            'price' => [
                'sometimes',
                'numeric',
                'min:0'
            ],

            'discount_pct' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:100'
            ],

            'services_count' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'valid_days' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

        ];
    }
}