<?php

namespace Aghfatehi\Msegat\Events;

use Aghfatehi\Msegat\DTOs\OtpResponse;
use Illuminate\Foundation\Events\Dispatchable;

class OtpGenerated
{
    use Dispatchable;

    public function __construct(
        public string $number,
        public OtpResponse $response,
    ) {
    }
}
