<?php

namespace App\Http\Requests\OperationsRequest\PackageServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'package_id' => [
                'sometimes',
                'exists:packages,id'
            ],

            'service_id' => [
                'sometimes',
                'exists:services,id'
            ],

            'allowed_count' => [
                'sometimes',
                'integer',
                'min:0'
            ],

        ];
    }
}