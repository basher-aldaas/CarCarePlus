<?php

namespace App\Http\Requests\OperationsRequest\PackageRequest;

use App\Enums\PackageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'type' => [
                'required',
                Rule::in(PackageType::values())
            ],

            'price' => [
                'required',
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
                'required',
                'integer',
                'min:0'
            ],

            'valid_days' => [
                'required',
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