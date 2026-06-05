<?php

namespace Aghfatehi\Msegat\DTOs;

/**
 * Data transfer object representing an OTP send request.
 */
readonly class OtpRequest
{
    /**
     * @param  string  $number  REQUIRED. The recipient phone number.
     * @param  string  $sender  REQUIRED. Approved sender name.
     * @param  string  $language  OPTIONAL. OTP language: 'Ar' (Arabic, default) or 'En' (English).
     */
    public function __construct(
        public string $number,
        public string $sender,
        public string $language = 'Ar',
    ) {
    }

    /**
     * Convert the DTO to an array suitable for the Msegat OTP API.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'userSender' => $this->sender,
            'lang' => $this->language,
        ];
    }
}
