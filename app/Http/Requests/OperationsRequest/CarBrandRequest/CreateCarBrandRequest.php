<?php

namespace App\Http\Requests\OperationsRequest\CarBrandRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateCarBrandRequest extends FormRequest
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
                'max:255',
                'unique:car_brands,name'
            ],

            'logo' => [
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