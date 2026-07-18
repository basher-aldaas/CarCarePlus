<?php

namespace App\Http\Requests\OperationsRequest\CarsRequest;

use App\Enums\CarEnums\FuelType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarRequest extends FormRequest
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
     * All fields are optional on update; only the provided ones are validated.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand_id' => ['sometimes', 'integer', 'exists:car_brands,id'],
            'car_type_id' => ['sometimes', 'integer', 'exists:car_types,id'],
            'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
            'plate_number' => ['sometimes', 'string', 'max:255', Rule::unique('cars', 'plate_number')->ignore($this->route('id'))],
            'model' => ['sometimes', 'string', 'max:255'],
            'year' => ['sometimes', 'digits:4', 'integer', 'min:1900', 'max:' . date('Y')],
            'color' => ['sometimes', 'string', 'max:255'],
            'fuel_type' => ['sometimes', Rule::in(FuelType::values())],
            'cylinders' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:16'],
            'mileage' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}