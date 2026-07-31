<?php

namespace App\Http\Requests\OperationsRequest\SystemSettingRequest;

use App\Enums\SystemSettingType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSystemSettingRequest extends FormRequest
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
            'key' => [
                'required',
                'string',
                'max:255',
                'unique:system_settings,key'
            ],

            'value' => [
                'required',
                'string'
            ],

            'type' => [
                'required',
                'string',
                Rule::in(SystemSettingType::values()),
            ],

            'description' => [
                'nullable',
                'string'
            ],
        ];
    }
}
