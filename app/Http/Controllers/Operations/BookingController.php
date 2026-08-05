<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\OrderDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\BookingRequest\AssignBookingRequest;
use App\Http\Requests\OperationsRequest\BookingRequest\CancelBookingRequest;
use App\Http\Requests\OperationsRequest\BookingRequest\ConfirmBookingRequest;
use App\Http\Requests\OperationsRequest\BookingRequest\CreateBookingRequest;
use App\Http\Requests\OperationsRequest\BookingRequest\UpdateBookingRequest;
use App\Http\Resources\BranchResource;
use App\Http\Resources\OrderResource;
use App\Http\Responses\Response;
use App\Services\Operations\BookingQuoteService;
use App\Services\Operations\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        public BookingService $bookingService,
        public BookingQuoteService $bookingQuoteService,
    ) {}

    /**
     * List bookings, automatically scoped to the caller: a super admin sees
     * every booking, an admin sees their branch's, an employee sees the ones
     * assigned to them, and a customer sees only their own.
     */
    public function index(): JsonResponse
    {
        $result = $this->bookingService->getAllBookings();

        return Response::Success(
            data: OrderResource::collection($result),
            message: __('Bookings retrieved successfully')
        );
    }

    /**
     * Step 1 of booking: validate + price the submission (without creating
     * anything) and return a short-lived quote_token plus the branch info
     * and price receipt for the customer to review.
     */
    public function quote(CreateBookingRequest $request): JsonResponse
    {
        $result = $this->bookingQuoteService->quote($request->validated());

        return Response::Success(
            data: [
                'quote_token' => $result['quote_token'],
                'branch' => $result['branch'] ? new BranchResource($result['branch']) : null,
                'price_items' => $result['price_items'],
                'car_count' => $result['car_count'],
                'total_price_per_car' => $result['total_price_per_car'],
                'total_price' => $result['total_price'],
                'expires_at' => $result['expires_at'],
            ],
            message: __('Quote generated successfully')
        );
    }

    /**
     * Step 2 of booking: redeem the quote_token and actually create the
     * order(s) — one per car — sharing a booking_group_id.
     */
    public function confirm(ConfirmBookingRequest $request): JsonResponse
    {
        $orders = $this->bookingQuoteService->confirm($request->validated('quote_token'));

        return Response::Success(
            data: OrderResource::collection(collect($orders)),
            message: __('Booking confirmed successfully')
        );
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->bookingService->getBookingById($id);

        return Response::Success(
            data: new OrderResource($result),
            message: __('Booking retrieved successfully')
        );
    }

    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $dto = OrderDTO::fromArray($request->validated());
        $result = $this->bookingService->updateBooking($dto, $id);

        return Response::Success(
            data: new OrderResource($result),
            message: __('Booking updated successfully')
        );
    }

    /**
     * Cancel the booking. Accepts an optional cancel_reason in the body.
     */
    public function destroy(CancelBookingRequest $request, int $id): JsonResponse
    {
        $result = $this->bookingService->cancelBooking($id, $request->validated('cancel_reason'));

        return Response::Success(
            data: new OrderResource($result),
            message: __('Booking cancelled successfully')
        );
    }

    public function assign(AssignBookingRequest $request, int $id): JsonResponse
    {
        $result = $this->bookingService->assignBooking($id, (int) $request->validated('employee_id'));

        return Response::Success(
            data: new OrderResource($result),
            message: __('Booking assigned successfully')
        );
    }

    public function start(int $id): JsonResponse
    {
        $result = $this->bookingService->startBooking($id);

        return Response::Success(
            data: new OrderResource($result),
            message: __('Booking started successfully')
        );
    }

    public function complete(int $id): JsonResponse
    {
        $result = $this->bookingService->completeBooking($id);

        return Response::Success(
            data: new OrderResource($result),
            message: __('Booking completed successfully')
        );
    }
}
