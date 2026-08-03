<?php


namespace App\Http\Requests\OperationsRequest\WorkshopRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => ['sometimes', 'string', 'max:255'],

            'name_ar' => ['sometimes', 'string', 'max:255'],

            'address' => ['sometimes', 'string'],

            'city' => ['sometimes', 'string', 'max:255'],

            'latitude' => ['nullable', 'numeric'],

            'longitude' => ['nullable', 'numeric'],

        ];
    }
}
