<?php

namespace App\Http\Requests\OperationsRequest\PackageServiceSubServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreatePackageServiceSubServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'package_service_id' => [
                'required',
                'exists:package_services,id'
            ],

            'sub_service_id' => [
                'required',
                'exists:sub_services,id'
            ],

            'price_override' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

        ];
    }
}