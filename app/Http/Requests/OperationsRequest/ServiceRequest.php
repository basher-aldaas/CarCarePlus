<?php


namespace App\Http\Requests\OperationsRequest;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'category_id' => [
                'required',
                'exists:categories,id'
            ],


            'name' => [
                'required',
                'string',
                'max:255'
            ],


            'name_ar' => [
                'required',
                'string',
                'max:255'
            ],


            'description' => [
                'nullable',
                'string'
            ],


            'base_price' => [
                'required',
                'numeric',
                'min:0'
            ],


            'is_vip_available' => [
                'required',
                'boolean'
            ],


            'vip_extra_price' => [
                'nullable',
                'numeric',
                'min:0'
            ],


            'duration_minutes' => [
                'required',
                'integer',
                'min:1'
            ],

        ];
    }
}
