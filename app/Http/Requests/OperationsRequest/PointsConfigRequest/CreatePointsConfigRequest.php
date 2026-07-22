<?php

namespace App\Http\Requests\OperationsRequest\PointsConfigRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreatePointsConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'earn_per_amount' => [
                'required',
                'numeric',
                'min:0'
            ],

            'redeem_value' => [
                'required',
                'numeric',
                'min:0'
            ],

            'min_redeem' => [
                'required',
                'integer',
                'min:0'
            ],

            'expiry_days' => [
                'required',
                'integer',
                'min:0'
            ],

            'max_earn_per_order' => [
                'required',
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