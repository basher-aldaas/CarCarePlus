<?php

namespace App\Http\Requests\OperationsRequest\BookingRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
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
     * Only the booking's own details may be edited here — status, employee
     * assignment and pricing go through their dedicated endpoints instead.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'car_id' => ['sometimes', 'integer', 'exists:cars,id'],
            'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
            'scheduled_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:now'],
            'location_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'location_address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}