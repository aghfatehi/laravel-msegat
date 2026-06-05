<?php

namespace Aghfatehi\Msegat\Events;

use Aghfatehi\Msegat\DTOs\SmsResponse;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatched after an SMS message is successfully sent via the Msegat API.
 *
 * Listen for this event to log deliveries or trigger post-send workflows.
 */
class MessageSent
{
    use Dispatchable;

    /**
     * @param  SmsResponse  $response  The API response from the send operation.
     */
    public function __construct(
        public SmsResponse $response,
    ) {
    }
}
