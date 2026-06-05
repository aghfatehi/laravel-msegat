<?php

namespace Aghfatehi\Msegat\Exceptions;

/**
 * Base exception for all Msegat-related errors.
 *
 * All other exceptions in this package extend this class,
 * allowing you to catch generic Msegat failures.
 */
class MsegatException extends \RuntimeException
{
    /**
     * @param  string  $message  The exception message.
     * @param  int  $code  The exception code.
     * @param  \Throwable|null  $previous  Previous exception for chaining.
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
