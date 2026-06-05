<?php

namespace Aghfatehi\Msegat\Exceptions;

/**
 * Thrown when input validation fails in the manager or client.
 *
 * Indicates missing or malformed parameters before an API call is made.
 */
class ValidationException extends MsegatException
{
    /**
     * @param  string  $message  Description of the validation failure.
     * @param  int  $code  OPTIONAL. Exception code.
     */
    public function __construct(string $message = 'Validation failed', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
