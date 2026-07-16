<?php

namespace App\Http\Requests\OperationsRequest\CarsRequest;

use App\Enums\CarEnums\FuelType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddCarRequest extends FormRequest
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
            'brand_id' => ['required', 'integer', 'exists:car_brands,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'car_type_id' => ['required', 'integer', 'exists:car_types,id'],
            'plate_number' => ['required', 'string', 'max:255', 'unique:cars,plate_number'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => [ 'required', 'digits:4', 'integer', 'min:1900', 'max:' . date('Y'), ],
            'color' => ['required', 'string', 'max:255'],
            'fuel_type' => ['required', Rule::in(FuelType::values())],
            'cylinders' => ['nullable', 'integer', 'min:1', 'max:16'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'image' => [ 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', ],
        ];
    }
}
