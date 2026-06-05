<?php

namespace Aghfatehi\Msegat\Http\Middleware;

use Aghfatehi\Msegat\Exceptions\WebhookSignatureException;
use Closure;
use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('msegat.webhook.secret');

        if (! empty($secret)) {
            $signature = $request->header('X-Msegat-Signature');
            $timestamp = $request->header('X-Msegat-Timestamp');

            if (! $signature || ! $timestamp) {
                throw new WebhookSignatureException('Missing signature headers');
            }

            $tolerance = config('msegat.webhook.tolerance', 300);
            if (abs(time() - (int) $timestamp) > $tolerance) {
                throw new WebhookSignatureException('Webhook timestamp outside tolerance window');
            }

            $payload = $request->getContent();
            $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

            if (! hash_equals($expected, $signature)) {
                throw new WebhookSignatureException('Invalid webhook signature');
            }
        }

        return $next($request);
    }
}
