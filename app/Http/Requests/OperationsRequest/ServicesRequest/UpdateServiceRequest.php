<?php


namespace App\Http\Requests\OperationsRequest\ServicesRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'category_id' => [
                'sometimes',
                'exists:categories,id'
            ],


            'name' => [
                'sometimes',
                'string',
                'max:255'
            ],


            'name_ar' => [
                'sometimes',
                'string',
                'max:255'
            ],


            'description' => [
                'sometimes',
                'nullable',
                'string'
            ],


            'base_price' => [
                'sometimes',
                'numeric',
                'min:0'
            ],


            'is_vip_available' => [
                'sometimes',
                'boolean'
            ],


            'vip_extra_price' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0'
            ],


            'duration_minutes' => [
                'sometimes',
                'integer',
                'min:1'
            ],

        ];
    }
}
