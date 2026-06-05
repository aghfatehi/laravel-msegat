<?php

namespace Aghfatehi\Msegat\DTOs;

readonly class DeliveryReport
{
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
