<?php

namespace Aghfatehi\Msegat\Exceptions;

class ApiException extends MsegatException
{
    private string $apiCode;

    public function __construct(string $apiCode, string $message = '', int $code = 0)
    {
        $this->apiCode = $apiCode;
        parent::__construct($message ?: "API error: {$apiCode}", $code);
    }

    public function getApiCode(): string
    {
        return $this->apiCode;
    }
}
