<?php

namespace Aghfatehi\Msegat\DTOs;

readonly class OtpResponse
{
    public function __construct(
        public bool $successful,
        public string $status,
        public ?string $otpId = null,
        public array $raw = [],
    ) {
    }

    public static function fromApiResponse(array $response): self
    {
        $code = $response['code'] ?? '';
        $isSuccess = $code === '1' || $code === 'M0000';

        return new self(
            successful: $isSuccess,
            status: $isSuccess ? 'sent' : 'failed',
            otpId: $response['id'] ?? null,
            raw: $response,
        );
    }
}
