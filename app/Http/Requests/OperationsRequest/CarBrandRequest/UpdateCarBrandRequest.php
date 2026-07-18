<?php

namespace App\Http\Requests\OperationsRequest\CarBrandRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarBrandRequest extends FormRequest
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
                'max:255',
                Rule::unique('car_brands', 'name')
                    ->ignore($this->route('car_brand'))
            ],

            'logo' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

        ];
    }
}