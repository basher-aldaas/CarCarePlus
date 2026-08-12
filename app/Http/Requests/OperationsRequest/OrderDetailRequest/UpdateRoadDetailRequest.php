<?php

namespace App\Http\Requests\OperationsRequest\OrderDetailRequest;

use App\Enums\CarEnums\CarTypeSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoadDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'problem_type_id' => ['nullable', 'integer', 'exists:problem_types,id'],
            'car_type_size' => ['sometimes', Rule::enum(CarTypeSize::class)],
            'problem_description' => ['sometimes', 'string', 'max:5000'],
            'problem_image_url' => ['nullable', 'string', 'url'],
            'ai_diagnosis' => ['nullable', 'string', 'max:5000'],
        ];
    }
}