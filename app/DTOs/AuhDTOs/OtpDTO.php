<?php

namespace App\DTOs\AuhDTOs;

use App\Enums\OtpEnums\OtpChannel;
use App\Enums\OtpEnums\OtpType;
use Carbon\CarbonInterface;

/**
 * Data for creating an otp_codes row. The `code` is expected to already be
 * hashed by the caller before persistence.
 */
class OtpDTO
{
    public function __construct(
        public int $user_id,
        public string $code,
        public OtpType $type,
        public OtpChannel $channel,
        public CarbonInterface $expires_at,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id'    => $this->user_id,
            'code'       => $this->code,
            'type'       => $this->type->value,
            'channel'    => $this->channel->value,
            'expires_at' => $this->expires_at,
        ];
    }
}
