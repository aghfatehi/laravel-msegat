<?php

namespace Aghfatehi\Msegat\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatched when a webhook callback is received from Msegat.
 *
 * The $type indicates the webhook category: 'delivery', 'status', 'incoming', or 'failed'.
 */
class WebhookReceived
{
    use Dispatchable;

    /**
     * @param  string  $type  Webhook category ('delivery', 'status', 'incoming', 'failed').
     * @param  array<string,mixed>  $payload  The webhook payload data.
     */
    public function __construct(
        public string $type,
        public array $payload,
    ) {
    }
}
