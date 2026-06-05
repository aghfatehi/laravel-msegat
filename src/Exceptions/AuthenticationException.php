<?php

namespace Aghfatehi\Msegat\Exceptions;

/**
 * Thrown when Msegat API credentials are invalid or rejected.
 *
 * Corresponds to Msegat error codes M0002 / 1020.
 */
class AuthenticationException extends MsegatException
{
    /**
     * @param  string  $message  OPTIONAL. Custom error message.
     * @param  int  $code  OPTIONAL. Exception code (default 1020).
     */
    public function __construct(string $message = 'Invalid Msegat API credentials', int $code = 1020)
    {
        parent::__construct($message, $code);
    }
}
