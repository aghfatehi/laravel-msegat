<?php

namespace Aghfatehi\Msegat\Exceptions;

/**
 * Thrown when a webhook request has a missing or invalid signature.
 *
 * Indicates the request may not be from Msegat.
 */
class WebhookSignatureException extends MsegatException
{
    /**
     * @param  string  $message  OPTIONAL. Custom error message.
     * @param  int  $code  OPTIONAL. Exception code (default 401).
     */
    public function __construct(string $message = 'Invalid webhook signature', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
