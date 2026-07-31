<?php

namespace App\Http\Requests\OperationsRequest\InventoryRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryRequest extends FormRequest
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
                // Super admin must name the branch explicitly; an admin's
                // branch is resolved server-side, so this is not required for him.
                Rule::requiredIf(fn () => (bool) $this->user()?->hasRole('super_admin')),
                'nullable',
                'integer',
                'exists:branches,id',
                Rule::unique('inventories')->where(fn ($query) => $query->where('material_id', $this->input('material_id'))),
            ],

            'material_id' => [
                'required',
                'integer',
                'exists:materials,id'
            ],

            'quantity' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'min_quantity' => [
                'nullable',
                'numeric',
                'min:0'
            ],
        ];
    }
}
