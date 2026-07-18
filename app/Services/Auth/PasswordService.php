<?php

namespace App\Services\Auth;

use App\DTOs\AuhDTOs\ForgotPasswordDTO;
use App\DTOs\AuhDTOs\ResetPasswordDTO;
use App\DTOs\AuhDTOs\ResetPasswordOtpDTO;
use App\Enums\OtpEnums\OtpChannel;
use App\Enums\OtpEnums\OtpType;
use App\Exceptions\InvalidOtpException;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\PasswordResetOtpNotification;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected OtpService $otpService,
    ) {}

    // ----- Token-link flow -----

    /**
     * Send a password reset link to the given email.
     * Returns the broker status string (e.g. Password::RESET_LINK_SENT).
     */
    public function sendResetLink(ForgotPasswordDTO $dto): string
    {
        return Password::sendResetLink(['email' => $dto->email]);
    }

    /**
     * Complete a password reset using the emailed token.
     * On success: revokes all existing tokens and emails a confirmation.
     * Returns the broker status string (Password::PASSWORD_RESET on success).
     */
    public function reset(ResetPasswordDTO $dto): string
    {
        return Password::reset($dto->toCredentials(), function (User $user, string $password) {
            $this->applyNewPassword($user, $password);
            $user->tokens()->delete();
            $user->notify(new PasswordChangedNotification());
        });
    }

    // ----- OTP flow -----

    /**
     * Email a one-time reset code to the account (if it exists).
     * Silent for unknown emails to prevent account enumeration.
     */
    public function sendResetOtp(ForgotPasswordDTO $dto): void
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user) {
            return;
        }

        $code = $this->otpService->generate($user, OtpType::RESET_PASSWORD, OtpChannel::EMAIL);

        $user->notify(new PasswordResetOtpNotification($code));
    }

    /**
     * Reset the password using an emailed OTP code.
     * On success: revokes all existing tokens and emails a confirmation.
     *
     * @throws InvalidOtpException on unknown email or an invalid/expired code.
     */
    public function resetWithOtp(ResetPasswordOtpDTO $dto): void
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user || ! $this->otpService->verify($user, $dto->otp, OtpType::RESET_PASSWORD)) {
            throw new InvalidOtpException();
        }

        $this->applyNewPassword($user, $dto->password);
        $user->tokens()->delete();
        $user->notify(new PasswordChangedNotification());
    }

    private function applyNewPassword(User $user, string $password): void
    {
        $user->forceFill(['password' => Hash::make($password)])
            ->setRememberToken(Str::random(60));
        $user->save();
    }
}
