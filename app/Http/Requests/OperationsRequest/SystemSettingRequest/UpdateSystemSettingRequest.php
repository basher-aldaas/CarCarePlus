<?php

namespace App\Http\Requests\OperationsRequest\SystemSettingRequest;

use App\Enums\SystemSettingType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemSettingRequest extends FormRequest
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
        $systemSettingId = $this->route('system_setting')?->id;

        return [
            'key' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('system_settings', 'key')->ignore($systemSettingId),
            ],

            'value' => [
                'sometimes',
                'string'
            ],

            'type' => [
                'sometimes',
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
