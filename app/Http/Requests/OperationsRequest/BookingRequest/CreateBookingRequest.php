<?php

namespace App\Http\Requests\OperationsRequest\BookingRequest;

use App\Enums\CarEnums\CarTypeSize;
use App\Enums\PaymentEnums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'car_ids' => ['required', 'array', 'min:1'],
            'car_ids.*' => ['distinct', 'integer', 'exists:cars,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'booking_type' => ['required', 'boolean'],
            'is_vip' => ['sometimes', 'boolean'],
            'scheduled_at' => [
                'required_if:booking_type,0',
                'prohibited_if:booking_type,1',
                'nullable',
                'date',
                'after_or_equal:now',
            ],            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'location_address' => ['nullable', 'string', 'max:500'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],//يجب حذفها لان النظام ياخذها من خلال المسافة بين موقع العميل والفرع
            'notes' => ['nullable', 'string', 'max:1000'],

            'sub_service_ids' => ['nullable', 'array'],
            'sub_service_ids.*' => ['distinct', 'integer', 'exists:sub_services,id'],

            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['distinct', 'required', 'integer', 'exists:materials,id'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.01'],

            'payment_method' => ['required', Rule::in(PaymentMethod::values())],

            // Road assistance only — required together when problem_type_id is sent.
            'problem_type_id' => ['nullable', 'integer', 'exists:problem_types,id'],
            'car_type_size' => ['required_with:problem_type_id', Rule::in(CarTypeSize::values())],
            'problem_description' => ['required_with:problem_type_id', 'string', 'max:1000'],
            'problem_image_url' => ['nullable', 'string', 'max:2000'],
            'towing_required' => ['sometimes', 'boolean'],

            // Flatbed towing only — TowingBookingHandler enforces these as
            // required when the booked category is towing.
            'destination_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
