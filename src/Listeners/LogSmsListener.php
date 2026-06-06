<?php

namespace Aghfatehi\Msegat\Listeners;

use Aghfatehi\Msegat\Events\MessageSent;
use Illuminate\Support\Facades\Log;

/**
 * Listener that logs SMS send results to the configured log channel.
 *
 * Only logs when config('msegat.logging.enabled') is true.
 */
class LogSmsListener
{
    /**
     * Handle the MessageSent event.
     *
     * @param  MessageSent  $event  The event containing the SMS response.
     */
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
