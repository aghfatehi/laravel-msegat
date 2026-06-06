<?php

namespace Aghfatehi\Msegat\DTOs;

/**
 * Data transfer object representing the result of an OTP send/verify operation.
 */
readonly class OtpResponse
{
    /**
     * @param  bool  $successful  Whether the operation succeeded.
     * @param  string  $status  Result status: 'sent', 'verified', or 'failed'.
     * @param  string|null  $otpId  OPTIONAL. The OTP identifier returned by the API.
     * @param  array<string,mixed>  $raw  OPTIONAL. The raw API response for debugging.
     */
    public function __construct(
        public bool $successful,
        public string $status,
        public ?string $otpId = null,
        public array $raw = [],
    ) {
    }

    /**
     * Create an OtpResponse from the API's JSON-decoded response array.
     *
     * @param  array<string,mixed>  $response  The raw API response.
     */
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
