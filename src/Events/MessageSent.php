<?php

namespace Aghfatehi\Msegat\Events;

use Aghfatehi\Msegat\DTOs\SmsResponse;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent
{
    use Dispatchable;

    public function __construct(
        public SmsResponse $response,
    ) {
    }
}
