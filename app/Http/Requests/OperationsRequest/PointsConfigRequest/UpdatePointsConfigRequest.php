<?php

namespace App\Http\Requests\OperationsRequest\PointsConfigRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePointsConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'earn_per_amount' => [
                'sometimes',
                'numeric',
                'min:0'
            ],

            'redeem_value' => [
                'sometimes',
                'numeric',
                'min:0'
            ],

            'min_redeem' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'expiry_days' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'max_earn_per_order' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

        ];
    }
}