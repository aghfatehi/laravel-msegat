<?php

namespace Aghfatehi\Msegat\Exceptions;

class MsegatException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
