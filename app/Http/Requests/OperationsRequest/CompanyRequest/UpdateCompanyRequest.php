<?php


namespace App\Http\Requests\OperationsRequest\CompanyRequest;


use Illuminate\Foundation\Http\FormRequest;


class UpdateCompanyRequest extends FormRequest
{


    public function authorize(): bool
    {
        return auth()->check();
    }


    public function rules(): array
    {

        return [

            'name' => [
                'sometimes',
                'string',
                'max:255'
            ],


            'name_ar' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],


            'commercial_reg' => [
                'sometimes',
                'string',
                'max:255'
            ],


            'tax_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],


            'address' => [
                'sometimes',
                'nullable',
                'string'
            ],


            /*
             فقط super admin
             */

            'status' => [
                'sometimes',
                'in:pending,approved,rejected'
            ],


            'is_active' => [
                'sometimes',
                'boolean'
            ],


        ];


    }


}
