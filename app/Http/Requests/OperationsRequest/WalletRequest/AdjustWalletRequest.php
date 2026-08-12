<?php

namespace App\Http\Requests\OperationsRequest\WalletRequest;

use Illuminate\Foundation\Http\FormRequest;

class AdjustWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Positive credits the wallet, negative debits it. Non-zero.
            'amount' => ['required', 'numeric', 'not_in:0', 'between:-1000000,1000000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}