<?php

namespace Aghfatehi\Msegat\DTOs;

readonly class BalanceResponse
{
    public function __construct(
        public bool $successful,
        public float $balance = 0.0,
        public string $raw = '',
    ) {
    }

    public static function fromRawResponse(string $body): self
    {
        $isError = in_array($body, ['M0002', '1020', 'M0001', '1010'], true);

        return new self(
            successful: ! $isError,
            balance: $isError ? 0.0 : (float) $body,
            raw: $body,
        );
    }
}
