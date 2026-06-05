<?php

namespace Aghfatehi\Msegat\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatched immediately before an SMS is sent to the Msegat API.
 *
 * The $data array can be modified by listeners (e.g. to add custom parameters).
 */
class MessageSending
{
    use Dispatchable;

    /**
     * @param  array<string,mixed>  $data  The prepared SMS payload about to be sent.
     */
    public function __construct(
        public array $data,
    ) {
    }
}
