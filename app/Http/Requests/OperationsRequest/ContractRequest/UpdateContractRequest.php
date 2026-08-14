<?php

namespace App\Http\Requests\OperationsRequest\ContractRequest;

use App\Enums\ContractStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractRequest extends FormRequest
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
                'sometimes',
                'integer',
                'exists:companies,id',
            ],

            'workshop_id' => [
                'sometimes',
                'integer',
                'exists:workshops,id',
            ],

            'title' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'value' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'start_date' => [
                'sometimes',
                'date',
            ],

            'end_date' => [
                'sometimes',
                'date',
                'after_or_equal:start_date',
            ],

            'terms' => [
                'sometimes',
                'string',
            ],

            'file_url' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'status' => [
                'sometimes',
                Rule::in(ContractStatus::values()),
            ],

            'renewal_count' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'last_renewed_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}