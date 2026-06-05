<?php

namespace Aghfatehi\Msegat\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WebhookReceived
{
    use Dispatchable;

    public function __construct(
        public string $type,
        public array $payload,
    ) {
    }
}
