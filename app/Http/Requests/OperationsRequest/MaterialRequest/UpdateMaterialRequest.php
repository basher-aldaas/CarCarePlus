<?php

namespace App\Http\Requests\OperationsRequest\MaterialRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
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
            'material_unit_id' => [
                'sometimes',
                'integer',
                'exists:material_units,id'
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
                'nullable',
                'string'
            ],

            'unit_price' => [
                'sometimes',
                'numeric',
                'min:0'
            ],

            'is_vip_material' => [
                'nullable',
                'boolean'
            ],

            'is_visible_to_customer' => [
                'nullable',
                'boolean'
            ],

            'is_active' => [
                'nullable',
                'boolean'
            ],
        ];
    }
}
