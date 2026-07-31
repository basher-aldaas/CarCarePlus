<?php

namespace App\Http\Requests\OperationsRequest\PricingRuleRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingRuleRequest extends FormRequest
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
            'pricing_rule_type_id' => [
                'sometimes',
                'integer',
                'exists:pricing_rule_types,id'
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

            'value' => [
                'sometimes',
                'numeric'
            ],

            'conditions' => [
                'nullable',
                'array'
            ],

            'is_active' => [
                'nullable',
                'boolean'
            ],
        ];
    }
}
