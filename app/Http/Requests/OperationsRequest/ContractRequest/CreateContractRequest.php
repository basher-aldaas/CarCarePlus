<?php

namespace App\Http\Requests\OperationsRequest\ContractRequest;

use App\Enums\ContractStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateContractRequest extends FormRequest
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
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'workshop_id' => [
                'required',
                'integer',
                'exists:workshops,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'value' => [
                'required',
                'numeric',
                'min:0',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'terms' => [
                'required',
                'string',
            ],

            'file_url' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'status' => [
                'nullable',
                Rule::in(ContractStatus::values()),
            ],
        ];
    }
}