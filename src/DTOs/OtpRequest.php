<?php

namespace Aghfatehi\Msegat\DTOs;

readonly class OtpRequest
{
    public function __construct(
        public string $number,
        public string $sender,
        public string $language = 'Ar',
    ) {
    }

    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'userSender' => $this->sender,
            'lang' => $this->language,
        ];
    }
}
