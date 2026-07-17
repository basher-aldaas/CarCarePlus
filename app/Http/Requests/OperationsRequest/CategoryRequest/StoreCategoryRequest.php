<?php


namespace App\Http\Requests\OperationsRequest\CategoryRequest;

use Illuminate\Foundation\Http\FormRequest;


class StoreCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'name' => 'required|string|max:255',

            'name_ar' => 'required|string|max:255',

            'description' => 'nullable|string',

            'is_active' => 'nullable|boolean',

        ];
    }
}
