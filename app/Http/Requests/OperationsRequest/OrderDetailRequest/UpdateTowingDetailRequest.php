<?php

namespace App\Http\Requests\OperationsRequest\OrderDetailRequest;

use App\Enums\CarEnums\CarTypeSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTowingDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'car_type_size' => ['sometimes', Rule::enum(CarTypeSize::class)],
            'destination_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_address' => ['sometimes', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}