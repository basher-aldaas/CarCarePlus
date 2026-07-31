<?php

namespace App\Http\Requests\OperationsRequest\InventoryRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryRequest extends FormRequest
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
        $inventoryId = $this->route('inventory')?->id;

        return [
            'branch_id' => [
                'sometimes',
                'integer',
                'exists:branches,id',
                Rule::unique('inventories')
                    ->where(fn ($query) => $query->where('material_id', $this->input('material_id')))
                    ->ignore($inventoryId),
            ],

            'material_id' => [
                'sometimes',
                'integer',
                'exists:materials,id'
            ],

            'quantity' => [
                'sometimes',
                'numeric',
                'min:0'
            ],

            'min_quantity' => [
                'sometimes',
                'numeric',
                'min:0'
            ],
        ];
    }
}
