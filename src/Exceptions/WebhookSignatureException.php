<?php

namespace Aghfatehi\Msegat\Exceptions;

class WebhookSignatureException extends MsegatException
{
    public function __construct(string $message = 'Invalid webhook signature', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
