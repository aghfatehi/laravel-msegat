<?php

namespace Aghfatehi\Msegat\Exceptions;

class ValidationException extends MsegatException
{
    public function __construct(string $message = 'Validation failed', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
