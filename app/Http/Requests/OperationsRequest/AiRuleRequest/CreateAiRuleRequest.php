<?php

namespace App\Http\Requests\OperationsRequest\AiRuleRequest;

use App\Enums\AiRuleConditionType;
use App\Enums\CarEnums\CarTypeSize;
use App\Enums\CarEnums\FuelType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAiRuleRequest extends FormRequest
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
            'brand_id' => [
                'nullable',
                'integer',
                'exists:car_brands,id'
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'name_ar' => [
                'required',
                'string',
                'max:255'
            ],

            'type' => [
                'required',
                'string',
                Rule::in(AiRuleConditionType::values()),
            ],

            'condition_key' => [
                'nullable',
                'string',
                'max:255'
            ],

            'condition_value' => [
                'nullable',
                'string',
                'max:255'
            ],

            'car_type' => [
                'nullable',
                'string',
                Rule::in(CarTypeSize::values()),
            ],

            'fuel_type' => [
                'nullable',
                'string',
                Rule::in(FuelType::values()),
            ],

            'response_template' => [
                'required',
                'string'
            ],

            'is_active' => [
                'nullable',
                'boolean'
            ],
        ];
    }
}
