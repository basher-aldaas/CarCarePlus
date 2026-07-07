<?php

namespace App\Services\Auth;

use App\DTOs\AuhDTOs\OtpDTO;
use App\Enums\OtpEnums\OtpChannel;
use App\Enums\OtpEnums\OtpType;
use App\Exceptions\OtpCooldownException;
use App\Models\User;
use App\Repositories\Eloquent\OtpRepository;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    /** Number of digits in a generated code. */
    private const CODE_LENGTH = 6;

    /** How long a code stays valid, in minutes. */
    private const EXPIRY_MINUTES = 10;

    /** Seconds a user must wait between requesting new codes of the same type. */
    private const RESEND_COOLDOWN_SECONDS = 60;

    /** Max verification attempts before a code is rejected. */
    private const MAX_ATTEMPTS = 5;

    public function __construct(protected OtpRepository $otpRepository) {}

    /**
     * Generate a fresh OTP for the user, invalidating any previous ones of the
     * same type. Persists the code hashed and returns the plaintext code so the
     * caller can deliver it (e.g. by email).
     *
     * @throws OtpCooldownException when requested again within the cooldown window.
     */
    public function generate(User $user, OtpType $type, OtpChannel $channel): string
    {
        $last = $this->otpRepository->latestForType($user->id, $type);

        if ($last) {
            $elapsed = $last->created_at->diffInSeconds(now());
            if ($elapsed < self::RESEND_COOLDOWN_SECONDS) {
                throw new OtpCooldownException(self::RESEND_COOLDOWN_SECONDS - $elapsed);
            }
        }

        $this->otpRepository->invalidateActive($user->id, $type);

        $plainCode = $this->generateCode();

        $this->otpRepository->create(new OtpDTO(
            user_id:    $user->id,
            code:       Hash::make($plainCode),
            type:       $type,
            channel:    $channel,
            expires_at: now()->addMinutes(self::EXPIRY_MINUTES),
        ));

        return $plainCode;
    }

    /**
     * Verify a submitted code against the user's latest active OTP of the type.
     * Consumes the code (marks it used) on success; counts a failed attempt otherwise.
     */
    public function verify(User $user, string $code, OtpType $type): bool
    {
        $otp = $this->otpRepository->latestActiveForType($user->id, $type);

        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $otp->code)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->update(['is_used' => true]);

        return true;
    }

    private function generateCode(): string
    {
        $max = (10 ** self::CODE_LENGTH) - 1;

        return str_pad((string) random_int(0, $max), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }
}
