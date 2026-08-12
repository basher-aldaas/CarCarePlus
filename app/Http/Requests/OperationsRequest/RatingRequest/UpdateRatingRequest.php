<?php

namespace App\Http\Requests\OperationsRequest\RatingRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_rating' => ['sometimes', 'integer', 'between:1,5'],
            'employee_rating' => ['nullable', 'integer', 'between:1,5'],
            'workshop_rating' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['string', 'url'],
        ];
    }
}