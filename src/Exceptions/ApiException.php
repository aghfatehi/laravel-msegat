<?php

namespace Aghfatehi\Msegat\Exceptions;

/**
 * Thrown when the Msegat API returns an error code.
 *
 * Carries the original API error code for programmatic handling.
 */
class ApiException extends MsegatException
{
    /** The Msegat API error code (e.g. 'M0001', '1020', '1060'). */
    private string $apiCode;

    /**
     * @param  string  $apiCode  The API error code returned by Msegat.
     * @param  string  $message  OPTIONAL. Human-readable message.
     * @param  int  $code  OPTIONAL. HTTP/exception code.
     */
    public function __construct(string $apiCode, string $message = '', int $code = 0)
    {
        $this->apiCode = $apiCode;
        parent::__construct($message ?: "API error: {$apiCode}", $code);
    }

    /**
     * Get the raw Msegat API error code.
     */
    public function getApiCode(): string
    {
        return $this->apiCode;
    }
}
