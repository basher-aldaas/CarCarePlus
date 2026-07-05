<?php

namespace App\Http\Controllers\Auth;

use App\Constants\HttpStatusConstants;
use App\DTOs\AuthDTOs\ChangePasswordDTO;
use App\DTOs\AuthDTOs\ForgotPasswordDTO;
use App\DTOs\AuthDTOs\ResetPasswordDTO;
use App\DTOs\AuthDTOs\ResetPasswordOtpDTO;
use App\Exceptions\PasswordResetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\ChangePasswordRequest;
use App\Http\Requests\AuthRequests\ForgotPasswordRequest;
use App\Http\Requests\AuthRequests\ResetPasswordOtpRequest;
use App\Http\Requests\AuthRequests\ResetPasswordRequest;
use App\Http\Responses\Response;
use App\Services\Auth\PasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function __construct(protected PasswordService $passwordService) {}

    /**
     * Link flow, step 1 — request a reset link by email.
     * Always responds generically so email addresses cannot be enumerated.
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->passwordService->sendResetLink(
            ForgotPasswordDTO::fromArray($request->validated())
        );

        if ($status === Password::RESET_THROTTLED) {
            return Response::Error(
                data: null,
                message: __('Please wait before requesting another reset link.'),
                code: HttpStatusConstants::HTTP_429_TOO_MANY_REQUESTS
            );
        }

        return Response::Success(
            data: null,
            message: __('If an account exists for this email, a password reset link has been sent.')
        );
    }

    /**
     * Link flow, step 2 — set a new password using the emailed token.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->passwordService->reset(
            ResetPasswordDTO::fromArray($request->validated())
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new PasswordResetException($this->messageForStatus($status));
        }

        return Response::Success(
            data: null,
            message: __('Your password has been reset successfully. Please log in again.')
        );
    }

    /**
     * OTP flow, step 1 — email a one-time reset code.
     * Responds generically to avoid account enumeration.
     */
    public function sendResetOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $this->passwordService->sendResetOtp(
            ForgotPasswordDTO::fromArray($request->validated())
        );

        return Response::Success(
            data: null,
            message: __('If an account exists for this email, a reset code has been sent.')
        );
    }

    /**
     * OTP flow, step 2 — set a new password using the emailed code.
     */
    public function resetWithOtp(ResetPasswordOtpRequest $request): JsonResponse
    {
        $this->passwordService->resetWithOtp(
            ResetPasswordOtpDTO::fromArray($request->validated())
        );

        return Response::Success(
            data: null,
            message: __('Your password has been reset successfully. Please log in again.')
        );
    }

    /**
     * Change the password for the authenticated user.
     */
    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $this->passwordService->change(
            $request->user(),
            ChangePasswordDTO::fromArray($request->validated())
        );

        return Response::Success(
            data: null,
            message: __('Your password has been changed successfully.')
        );
    }

    private function messageForStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => __('This password reset token is invalid or has expired.'),
            Password::INVALID_USER  => __('We cannot find a user with that email address.'),
            Password::RESET_THROTTLED => __('Please wait before retrying.'),
            default => __('Unable to reset the password.'),
        };
    }
}
