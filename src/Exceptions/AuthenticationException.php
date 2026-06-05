<?php

namespace Aghfatehi\Msegat\Exceptions;

class AuthenticationException extends MsegatException
{
    public function __construct(string $message = 'Invalid Msegat API credentials', int $code = 1020)
    {
        parent::__construct($message, $code);
    }
}
