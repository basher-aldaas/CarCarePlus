<?php

namespace App\Http\Requests\OperationsRequest\BookingRequest;

use App\Enums\PaymentEnums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
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
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'booking_type' => ['required', 'boolean'],
            'scheduled_at' => ['required_if_declined:booking_type', 'prohibited_if_accepted:booking_type', 'nullable', 'date', 'after_or_equal:now'],

            // Two mutually-exclusive ways to place the booking:
            //
            // Flow A — book at my current spot: the customer opens GPS and sends
            //   location_lat + location_lng. The nearest active branch is derived
            //   server-side (see BookingQuoteService::resolveBranchAndDistance).
            //   Required together, and required only when no branch_id was chosen.
            'location_lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:location_lng', 'required_without:branch_id'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:location_lat', 'required_without:branch_id'],

            // Flow B — book somewhere else: the customer skips GPS, types the
            //   address they'll go to (location_address) and picks a branch from
            //   the map. When no GPS is shared both are required — the branch to
            //   serve the booking and the address the customer will bring the car to.
            'branch_id' => ['nullable', 'integer', 'exists:branches,id', 'required_without:location_lat'],
            'location_address' => ['nullable', 'string', 'max:500', 'required_without:location_lat'],


            'is_vip' => ['sometimes', 'boolean'],


            // booking_type is a boolean (1/true = immediate, 0/false = scheduled).
            // The *_declined / *_accepted variants evaluate truthiness, so they fire
            // for JSON booleans — unlike required_if/prohibited_if, which only match
            // the literal strings "0"/"1".
            // when the customer sends their location the system auto-selects the
            // nearest branch (unless they picked a branch_id) and derives distance_km
            // distance_km is derived server-side from the customer's location and the
            // chosen branch — never accepted from the client
            'notes' => ['nullable', 'string', 'max:1000'],

            'sub_service_ids' => ['nullable', 'array'],
            'sub_service_ids.*' => ['distinct', 'integer', 'exists:sub_services,id'],

            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['distinct', 'required', 'integer', 'exists:materials,id'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.01'],

            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            //only meaningful once payment_method=package; omit it to get the list of eligible packages back instead of a priced quote
            'user_package_id' => ['nullable', 'integer', 'exists:user_packages,id'],

            // Road assistance only — required together when problem_type_id is sent.
            // car_type_size is never accepted from the client — it's derived
            // server-side from the customer's own car (see RoadAssistanceBookingHandler).
            'problem_type_id' => ['nullable', 'integer', 'exists:problem_types,id'],
            'problem_description' => ['required_with:problem_type_id', 'string', 'max:1000'],
            'problem_image_url' => ['nullable', 'string', 'max:2000'],

            // Flatbed towing only — TowingBookingHandler enforces these as
            // required when the booked category is towing.
            'destination_lat' => ['nullable', 'numeric', 'between:-90,90'],// الموظف بس يودي السيارة مطرح ما بدو الزبون بس بيفتح المزقع وبخزن الاحداثيات
            'destination_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_address' => ['nullable', 'string', 'max:500'], //بحط لوين بدو ياخدها سواء كان عالمصلح او على الفرع او على بيته والموظف بس يجي بعالجه

            // Maintenance only — the workshop the customer picked from the
            // nearby-workshops list. MaintenanceBookingHandler enforces this
            // as required (and active) when the booked category is maintenance.
            'workshop_id' => ['nullable', 'integer', 'exists:workshops,id'],
        ];
    }
}
