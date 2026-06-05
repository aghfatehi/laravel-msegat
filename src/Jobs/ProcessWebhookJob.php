<?php

namespace Aghfatehi\Msegat\Jobs;

use Aghfatehi\Msegat\Events\WebhookReceived;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private string $type,
        private array $payload,
    ) {
    }

    public function handle(): void
    {
        WebhookReceived::dispatch($this->type, $this->payload);
    }
}
