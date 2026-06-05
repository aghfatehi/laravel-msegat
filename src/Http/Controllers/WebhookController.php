<?php

namespace Aghfatehi\Msegat\Http\Controllers;

use Aghfatehi\Msegat\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function deliveryReport(Request $request)
    {
        $this->verifySignature($request);

        $payload = $request->all();

        Log::channel(config('msegat.logging.channel'))->info('Msegat webhook: delivery report received', $payload);

        if (config('msegat.queue.enabled')) {
            ProcessWebhookJob::dispatch('delivery', $payload)
                ->onConnection(config('msegat.queue.connection'))
                ->onQueue(config('msegat.queue.queue'));
        }

        return response()->json(['success' => true]);
    }

    public function status(Request $request)
    {
        $this->verifySignature($request);

        $payload = $request->all();

        Log::channel(config('msegat.logging.channel'))->info('Msegat webhook: status update received', $payload);

        if (config('msegat.queue.enabled')) {
            ProcessWebhookJob::dispatch('status', $payload)
                ->onConnection(config('msegat.queue.connection'))
                ->onQueue(config('msegat.queue.queue'));
        }

        return response()->json(['success' => true]);
    }

    public function incoming(Request $request)
    {
        $this->verifySignature($request);

        $payload = $request->all();

        Log::channel(config('msegat.logging.channel'))->info('Msegat webhook: incoming message received', $payload);

        if (config('msegat.queue.enabled')) {
            ProcessWebhookJob::dispatch('incoming', $payload)
                ->onConnection(config('msegat.queue.connection'))
                ->onQueue(config('msegat.queue.queue'));
        }

        return response()->json(['success' => true]);
    }

    public function failed(Request $request)
    {
        $this->verifySignature($request);

        $payload = $request->all();

        Log::channel(config('msegat.logging.channel'))->warning('Msegat webhook: failed message received', $payload);

        if (config('msegat.queue.enabled')) {
            ProcessWebhookJob::dispatch('failed', $payload)
                ->onConnection(config('msegat.queue.connection'))
                ->onQueue(config('msegat.queue.queue'));
        }

        return response()->json(['success' => true]);
    }

    private function verifySignature(Request $request): void
    {
        $secret = config('msegat.webhook.secret');

        if (empty($secret)) {
            return;
        }

        $signature = $request->header('X-Msegat-Signature');
        $timestamp = $request->header('X-Msegat-Timestamp');

        if (! $signature || ! $timestamp) {
            abort(401, 'Missing signature headers');
        }

        $tolerance = config('msegat.webhook.tolerance', 300);
        if (abs(time() - (int) $timestamp) > $tolerance) {
            abort(401, 'Webhook timestamp outside tolerance window');
        }

        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid webhook signature');
        }
    }
}
