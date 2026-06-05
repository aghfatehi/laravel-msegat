<?php

namespace Aghfatehi\Msegat\DTOs;

/**
 * Data transfer object representing a single message delivery report.
 *
 * Usually received via webhook from Msegat.
 */
readonly class DeliveryReport
{
    /**
     * @param  string  $messageId  REQUIRED. Unique message identifier.
     * @param  string  $number  REQUIRED. The recipient phone number.
     * @param  string  $status  REQUIRED. Delivery status (e.g. delivered, failed, pending).
     * @param  string|null  $deliveredAt  OPTIONAL. Timestamp of delivery.
     * @param  string|null  $failureReason  OPTIONAL. Reason if delivery failed.
     * @param  array<string,mixed>  $raw  OPTIONAL. The raw webhook payload.
     */
    public function __construct(
        public string $messageId,
        public string $number,
        public string $status,
        public ?string $deliveredAt = null,
        public ?string $failureReason = null,
        public array $raw = [],
    ) {
    }
}
