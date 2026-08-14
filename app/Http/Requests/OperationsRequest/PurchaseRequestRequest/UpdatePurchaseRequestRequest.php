<?php

namespace App\Http\Requests\OperationsRequest\PurchaseRequestRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequestRequest extends FormRequest
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
        // notes and items are the only editable parts of a pending request.
        // When "items" is sent it replaces the whole item set, so each item's
        // fields are required; omit "items" entirely to leave them untouched.
        return [
            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'sometimes',
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

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }
}
