<?php

namespace App\Http\Requests\OperationsRequest\EmployeeReportRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],

            'problem_description' => [
                'required',
                'string',
            ],

            'affected_parts' => [
                'nullable',
                'array',
            ],
            'affected_parts.*' => [
                'string',
            ],

            'images' => [
                'nullable',
                'array',
            ],
            'images.*' => [
                'string',
            ],

            'recommendation' => [
                'nullable',
                'string',
            ],
        ];
    }
}