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
     * The customer may only adjust these four booking fields; everything else
     * (status, assignment, pricing inputs, location, …) goes through its own
     * dedicated endpoint. The booking_type/scheduled_at consistency and the
     * package-booking restriction are enforced in BookingService::updateBooking,
     * where the booking's existing state is available.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduled_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:now'],
            'booking_type' => ['sometimes', 'boolean'],
            'is_vip' => ['sometimes', 'boolean'],
            'sub_service_ids' => ['sometimes', 'nullable', 'array'],
            'sub_service_ids.*' => ['distinct', 'integer', 'exists:sub_services,id'],
        ];
    }
}