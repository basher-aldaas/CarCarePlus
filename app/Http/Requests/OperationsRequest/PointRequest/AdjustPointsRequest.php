<?php

namespace App\Http\Requests\OperationsRequest\PointRequest;

use App\Enums\PointsTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'customer_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],

            'type' => [
                'required',
                Rule::in(PointsTransactionType::values())
            ],

            'points' => [
                'required',
                'integer',
                'min:1'
            ],

            'note' => [
                'nullable',
                'string'
            ],

        ];
    }
}