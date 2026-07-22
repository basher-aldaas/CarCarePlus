<?php

namespace App\Http\Requests\OperationsRequest\PackageServiceSubServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageServiceSubServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'package_service_id' => [
                'sometimes',
                'exists:package_services,id'
            ],

            'sub_service_id' => [
                'sometimes',
                'exists:sub_services,id'
            ],

            'price_override' => [
                'sometimes',
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