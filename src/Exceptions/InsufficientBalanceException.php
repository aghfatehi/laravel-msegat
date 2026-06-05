<?php

namespace Aghfatehi\Msegat\Exceptions;

/**
 * Thrown when the Msegat account does not have enough SMS credits.
 *
 * Corresponds to Msegat error code 1060.
 */
class InsufficientBalanceException extends MsegatException
{
    /**
     * @param  string  $message  OPTIONAL. Custom error message.
     * @param  int  $code  OPTIONAL. Exception code (default 1060).
     */
    public function __construct(string $message = 'Insufficient Msegat account balance', int $code = 1060)
    {
        parent::__construct($message, $code);
    }
}
