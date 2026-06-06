<?php

namespace Aghfatehi\Msegat\Jobs;

use Aghfatehi\Msegat\Events\WebhookReceived;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued job that processes an incoming Msegat webhook.
 *
 * Dispatches a WebhookReceived event so your application can react
 * to delivery reports, status updates, or incoming messages.
 */
class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Maximum number of attempts. */
    public int $tries = 3;

    /**
     * @param  string  $type  REQUIRED. Webhook type: 'delivery', 'status', 'incoming', or 'failed'.
     * @param  array<string,mixed>  $payload  REQUIRED. The webhook payload data.
     */
    public function __construct(
        private string $type,
        private array $payload,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        WebhookReceived::dispatch($this->type, $this->payload);
    }
}
