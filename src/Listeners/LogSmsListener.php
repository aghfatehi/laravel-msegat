<?php

namespace Aghfatehi\Msegat\Listeners;

use Aghfatehi\Msegat\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSmsListener
{
    public function handle(MessageSent $event): void
    {
        if (!config('msegat.logging.enabled')) {
            return;
        }

        $response = $event->response;

        Log::channel(config('msegat.logging.channel'))
            ->info('Msegat SMS '.($response->successful ? 'sent' : 'failed'), [
                'code' => $response->code,
                'message' => $response->message,
                'bulk_id' => $response->bulkId,
            ]);
    }
}
