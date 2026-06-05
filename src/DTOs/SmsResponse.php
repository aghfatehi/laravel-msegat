<?php

namespace Aghfatehi\Msegat\DTOs;

readonly class SmsResponse
{
    public function __construct(
        public bool $successful,
        public string $code,
        public string $message,
        public ?string $bulkId = null,
        public array $raw = [],
    ) {
    }

    public static function fromApiResponse(array $response, bool $requireBulkId = false): self
    {
        $code = $response['code'] ?? 'M0001';
        $bulkId = null;

        if ($requireBulkId && isset($response['code']) && str_contains($response['code'], '-')) {
            $parts = explode('-', $response['code'], 2);
            $code = $parts[0];
            $bulkId = $parts[1] ?? null;
        }

        return new self(
            successful: $code === '1' || $code === 'M0000',
            code: $code,
            message: $response['message'] ?? ($code === '1' || $code === 'M0000' ? 'Success' : 'Unknown error'),
            bulkId: $bulkId,
            raw: $response,
        );
    }
}
