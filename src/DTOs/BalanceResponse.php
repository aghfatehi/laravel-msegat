<?php

namespace Aghfatehi\Msegat\DTOs;

/**
 * Data transfer object representing the account balance check result.
 */
readonly class BalanceResponse
{
    /**
     * @param  bool  $successful  Whether the balance check succeeded.
     * @param  float  $balance  OPTIONAL. Number of remaining SMS credits.
     * @param  string  $raw  OPTIONAL. The raw API response body.
     */
    public function __construct(
        public bool $successful,
        public float $balance = 0.0,
        public string $raw = '',
    ) {
    }

    /**
     * Create a BalanceResponse from the raw API response string.
     *
     * @param  string  $body  The raw response body (credit count or error code).
     */
    public static function fromRawResponse(string $body): self
    {
        $isError = in_array($body, ['M0002', '1020', 'M0001', '1010'], true);

        return new self(
            successful: !$isError,
            balance: $isError ? 0.0 : (float) $body,
            raw: $body,
        );
    }
}
