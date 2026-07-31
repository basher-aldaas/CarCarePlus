<?php

namespace App\Http\Requests\OperationsRequest\InventoryTransactionRequest;

use App\Enums\InventoryTransactionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryTransactionRequest extends FormRequest
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
            'branch_id' => [
                // Super admin must name the branch explicitly; an admins
                // transactions always originate from the branch he manages,
                // resolved server-side, so this is not required for him.
                Rule::requiredIf(fn () => (bool) $this->user()?->hasRole('super_admin')),
                'nullable',
                'integer',
                'exists:branches,id'
            ],

            'destination_branch_id' => [
                'nullable',
                'required_if:type,' . InventoryTransactionType::TRANSFER_OUT->value,
                'prohibited_unless:type,' . InventoryTransactionType::TRANSFER_OUT->value,
                'integer',
                'exists:branches,id',
                'different:branch_id',
            ],

            'material_id' => [
                'required',
                'integer',
                'exists:materials,id'
            ],

            'type' => [
                'required',
                'string',
                // transfer_in is a system-generated leg written for the
                // destination branch, not something a client can request.
                Rule::in([
                    InventoryTransactionType::IN->value,
                    InventoryTransactionType::OUT->value,
                    InventoryTransactionType::TRANSFER_OUT->value,
                ]),
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'reference_id' => [
                'nullable',
                'string',
                'max:255'
            ],

            'note' => [
                'nullable',
                'string'
            ],
        ];
    }
}
