<?php

namespace App\Http\Requests\OperationsRequest\PackageServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreatePackageServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'package_id' => [
                'required',
                'exists:packages,id'
            ],

            'service_id' => [
                'required',
                'exists:services,id'
            ],

            'allowed_count' => [
                'required',
                'integer',
                'min:0'
            ],

        ];
    }
}