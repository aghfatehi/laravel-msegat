<?php

namespace Aghfatehi\Msegat\DTOs;

/**
 * Data transfer object representing the result of an SMS send operation.
 */
readonly class SmsResponse
{
    /**
     * @param  bool  $successful  Whether the API reported success.
     * @param  string  $code  The Msegat response code (e.g. '1', 'M0000', or error).
     * @param  string  $message  Human-readable result message.
     * @param  string|null  $bulkId  OPTIONAL. Bulk ID if requested and returned.
     * @param  array<string,mixed>  $raw  OPTIONAL. The raw API response for debugging.
     */
    public function __construct(
        public bool $successful,
        public string $code,
        public string $message,
        public ?string $bulkId = null,
        public array $raw = [],
    ) {
    }

    /**
     * Create an SmsResponse from the API's JSON-decoded response array.
     *
     * @param  array<string,mixed>  $response  The raw API response.
     * @param  bool  $requireBulkId  OPTIONAL. True to parse bulk ID from response code.
     * @return self
     */
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
