<?php

namespace Aghfatehi\Msegat\Events;

use Aghfatehi\Msegat\DTOs\OtpResponse;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatched after an OTP code is generated and sent to a recipient.
 */
class OtpGenerated
{
    use Dispatchable;

    /**
     * @param  string  $number  The recipient phone number.
     * @param  OtpResponse  $response  The API response from the OTP send operation.
     */
    public function __construct(
        public string $number,
        public OtpResponse $response,
    ) {
    }
}
