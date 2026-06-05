<?php

namespace Aghfatehi\Msegat\Http\Middleware;

use Aghfatehi\Msegat\Exceptions\WebhookSignatureException;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware that verifies Msegat webhook HMAC signatures.
 *
 * Validates the X-Msegat-Signature header against a SHA256 HMAC of
 * the timestamp and request body using the configured webhook secret.
 */
class VerifyWebhookSignature
{
    /**
     * Handle an incoming webhook request.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @param  Closure  $next  The next middleware handler.
     * @return mixed
     *
     * @throws WebhookSignatureException If signature is missing, expired, or invalid.
     */
    public function handle(Request $request, Closure $next)
    {
        $secret = config('msegat.webhook.secret');

        if (!empty($secret)) {
            $signature = $request->header('X-Msegat-Signature');
            $timestamp = $request->header('X-Msegat-Timestamp');

            if (!$signature || !$timestamp) {
                throw new WebhookSignatureException('Missing signature headers');
            }

            $tolerance = config('msegat.webhook.tolerance', 300);
            if (abs(time() - (int) $timestamp) > $tolerance) {
                throw new WebhookSignatureException('Webhook timestamp outside tolerance window');
            }

            $payload = $request->getContent();
            $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

            if (!hash_equals($expected, $signature)) {
                throw new WebhookSignatureException('Invalid webhook signature');
            }
        }

        return $next($request);
    }
}
