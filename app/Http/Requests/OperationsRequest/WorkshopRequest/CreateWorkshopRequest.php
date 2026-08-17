<?php


namespace App\Http\Requests\OperationsRequest\WorkshopRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                'unique:workshops,user_id'
            ],

            'name' => ['required', 'string', 'max:255'],

            'name_ar' => ['required', 'string', 'max:255'],

            'address' => ['required', 'string'],

            'city' => ['required', 'string', 'max:255'],

            'latitude' => ['nullable', 'numeric'],

            'longitude' => ['nullable', 'numeric'],

        ];
    }
}
