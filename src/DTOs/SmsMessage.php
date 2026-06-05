<?php

namespace Aghfatehi\Msegat\DTOs;

/**
 * Data transfer object representing an SMS message to be sent.
 *
 * Encapsulates all parameters for a single SMS send operation.
 */
readonly class SmsMessage
{
    /**
     * @param  array<int,string>  $numbers  REQUIRED. Recipient phone numbers.
     * @param  string  $message  REQUIRED. The SMS body text.
     * @param  string  $sender  REQUIRED. Approved sender name.
     * @param  string  $encoding  OPTIONAL. 'UTF8' (default) or 'UCS2'.
     * @param  string|null  $timeToSend  OPTIONAL. 'now' or 'later'.
     * @param  string|null  $exactTime  OPTIONAL. Scheduled datetime (Y-m-d H:i:s) when timeToSend='later'.
     * @param  bool  $requestBulkId  OPTIONAL. Whether to request a bulk ID.
     * @param  bool  $filterDuplicates  OPTIONAL. Filter duplicate messages (default true).
     */
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

    /**
     * Convert the DTO to an array suitable for the Msegat API.
     *
     * @return array<string,mixed>
     */
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
