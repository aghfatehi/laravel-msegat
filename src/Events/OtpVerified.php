<?php

namespace Aghfatehi\Msegat\Events;

use Aghfatehi\Msegat\DTOs\OtpResponse;
use Illuminate\Foundation\Events\Dispatchable;

class OtpVerified
{
    use Dispatchable;

    public function __construct(
        public string $number,
        public string $code,
        public OtpResponse $response,
    ) {
    }
}
