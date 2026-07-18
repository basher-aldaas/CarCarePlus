<?php

namespace App\Http\Requests\OperationsRequest\SubServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'service_id' => [
                'sometimes',
                'exists:services,id'
            ],

            'name' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'name_ar' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'price' => [
                'sometimes',
                'numeric',
                'min:0'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

        ];
    }
}