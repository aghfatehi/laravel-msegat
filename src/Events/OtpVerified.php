<?php

namespace Aghfatehi\Msegat\Events;

use Aghfatehi\Msegat\DTOs\OtpResponse;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatched after a user-submitted OTP code is verified against Msegat.
 */
class OtpVerified
{
    use Dispatchable;

    /**
     * @param  string  $number  The recipient phone number.
     * @param  string  $code  The OTP code that was verified.
     * @param  OtpResponse  $response  The API response from the verification.
     */
    public function __construct(
        public string $number,
        public string $code,
        public OtpResponse $response,
    ) {
    }
}
