<?php

namespace App\Http\Requests\OperationsRequest\OrderDetailRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workshop_id' => ['nullable', 'integer', 'exists:workshops,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}