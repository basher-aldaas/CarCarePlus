<?php

namespace App\Http\Requests\OperationsRequest\RatingRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'service_rating' => ['required', 'integer', 'between:1,5'],
            'employee_rating' => ['nullable', 'integer', 'between:1,5'],
            'workshop_rating' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['string', 'url'],
        ];
    }
}