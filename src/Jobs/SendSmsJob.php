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

/**
 * Queued job that sends an SMS via the Msegat API.
 *
 * Dispatched by MsegatManager::queue(). Retries up to 3 times with a 5-second backoff.
 */
class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Maximum number of attempts. */
    public int $tries = 3;

    /** Seconds to wait between retry attempts. */
    public int $backoff = 5;

    /**
     * @param  array<string,mixed>  $data  REQUIRED. The SMS payload (numbers, userSender, msg, etc.).
     */
    public function __construct(
        private array $data,
    ) {
    }

    /**
     * Execute the job.
     *
     * Dispatches MessageSending, sends via the client, dispatches MessageSent,
     * and fails on unsuccessful responses.
     *
     * @param  MsegatClient  $client  Auto-injected by Laravel's service container.
     *
     * @throws \RuntimeException If sending fails.
     */
    public function handle(MsegatClient $client): void
    {
        MessageSending::dispatch($this->data);

        $response = $client->send($this->data);

        $result = SmsResponse::fromApiResponse($response, !empty($this->data['reqBulkId']));

        MessageSent::dispatch($result);

        if (!$result->successful) {
            throw new \RuntimeException("SMS sending failed: {$result->message}");
        }
    }
}
