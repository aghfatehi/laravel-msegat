<?php

namespace Aghfatehi\Msegat\DTOs;

readonly class SmsMessage
{
    public function __construct(
        public array $numbers,
        public string $message,
        public string $sender,
        public string $encoding = 'UTF8',
        public ?string $timeToSend = null,
        public ?string $exactTime = null,
        public bool $requestBulkId = false,
        public bool $filterDuplicates = true,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'numbers' => implode(',', $this->numbers),
            'userSender' => $this->sender,
            'msg' => $this->message,
            'msgEncoding' => $this->encoding,
            'reqBulkId' => $this->requestBulkId,
            'reqFilter' => $this->filterDuplicates,
        ];

        if ($this->timeToSend === 'later' && $this->exactTime) {
            $data['timeToSend'] = 'later';
            $data['exactTime'] = $this->exactTime;
        }

        return $data;
    }
}
