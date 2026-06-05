<?php

namespace Aghfatehi\Msegat\Jobs;

use Aghfatehi\Msegat\DTOs\SmsResponse;
use Aghfatehi\Msegat\Events\MessageSending;
use Aghfatehi\Msegat\Events\MessageSent;
use Aghfatehi\Msegat\MsegatClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        private array $data,
    ) {
    }

    public function handle(MsegatClient $client): void
    {
        MessageSending::dispatch($this->data);

        $response = $client->send($this->data);

        $result = SmsResponse::fromApiResponse($response, ! empty($this->data['reqBulkId']));

        MessageSent::dispatch($result);

        if (! $result->successful) {
            throw new \RuntimeException("SMS sending failed: {$result->message}");
        }
    }
}
