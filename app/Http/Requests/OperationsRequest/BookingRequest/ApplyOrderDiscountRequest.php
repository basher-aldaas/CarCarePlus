<?php

namespace App\Http\Requests\OperationsRequest\BookingRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyOrderDiscountRequest extends FormRequest
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
     * A discount is expressed either as a fixed money amount or as a
     * percentage of the order's current total. The concrete bound against the
     * order total is enforced in the service, where the order is loaded.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => [
                'required',
                'numeric',
                'gt:0',
                'max:100',
            ],

            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
