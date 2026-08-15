<?php

namespace App\Http\Requests\OperationsRequest\OrderDetailRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The towing/flatbed driver submits the actual GPS coordinates of the
 * destination where the car was delivered. Both coordinates are required —
 * this records where the delivery really happened, not a plan.
 */
class SubmitTowingDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_lat' => ['required', 'numeric', 'between:-90,90'],
            'destination_lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}