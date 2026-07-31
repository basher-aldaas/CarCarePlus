<?php

namespace App\Http\Requests\OperationsRequest\MaterialUnitRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialUnitRequest extends FormRequest
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

            'is_decimal' => [
                'nullable',
                'boolean'
            ],
        ];
    }
}
