<?php


namespace App\Http\Requests\OperationsRequest\CarsRequest;

use App\Enums\CarEnums\FuelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [

            'brand_id' => [
                'sometimes',
                'integer',
                'exists:car_brands,id'
            ],

            'company_id' => [
                'sometimes',
                'integer',
                'exists:companies,id'
            ],

            'car_type_id' => [
                'sometimes',
                'integer',
                'exists:car_types,id'
            ],

            'plate_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('cars', 'plate_number')
                    ->ignore($this->route('id'))
            ],

            'brand' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'model' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'year' => [
                'sometimes',
                'digits:4',
                'integer',
                'min:1900',
                'max:' . date('Y')
            ],

            'color' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'fuel_type' => [
                'sometimes',
                Rule::in(FuelType::values())
            ],

            'cylinders' => [
                'nullable',
                'integer',
                'min:1',
                'max:16'
            ],

            'mileage' => [
                'nullable',
                'integer',
                'min:0'
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],

        ];
    }
}
