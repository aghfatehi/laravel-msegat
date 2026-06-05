<?php

namespace Aghfatehi\Msegat\Exceptions;

class InsufficientBalanceException extends MsegatException
{
    public function __construct(string $message = 'Insufficient Msegat account balance', int $code = 1060)
    {
        parent::__construct($message, $code);
    }
}
