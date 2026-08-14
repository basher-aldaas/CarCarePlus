<?php

namespace App\Http\Requests\OperationsRequest\PurchaseRequestRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferPurchaseRequestRequest extends FormRequest
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
            // Branch the materials are taken from.
            'from_branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            // Branch the materials are moved into (must differ from the source).
            'to_branch_id' => [
                'required',
                'integer',
                'different:from_branch_id',
                'exists:branches,id',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.material_id' => [
                'required',
                'integer',
                'exists:materials,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }
}