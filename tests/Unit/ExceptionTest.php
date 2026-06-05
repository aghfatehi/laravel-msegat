<?php

namespace Aghfatehi\Msegat\Tests\Unit;

use Aghfatehi\Msegat\Exceptions\ApiException;
use Aghfatehi\Msegat\Exceptions\AuthenticationException;
use Aghfatehi\Msegat\Exceptions\InsufficientBalanceException;
use Aghfatehi\Msegat\Exceptions\MsegatException;
use Aghfatehi\Msegat\Exceptions\ValidationException;
use Aghfatehi\Msegat\Exceptions\WebhookSignatureException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function test_all_exceptions_extend_base(): void
    {
        $this->assertInstanceOf(MsegatException::class, new AuthenticationException);
        $this->assertInstanceOf(MsegatException::class, new InsufficientBalanceException);
        $this->assertInstanceOf(MsegatException::class, new ValidationException);
        $this->assertInstanceOf(MsegatException::class, new WebhookSignatureException);
        $this->assertInstanceOf(MsegatException::class, new ApiException('M0002'));
    }

    public function test_api_exception_has_code(): void
    {
        $e = new ApiException('M0002');
        $this->assertSame('M0002', $e->getApiCode());
    }

    public function test_authentication_exception_default_message(): void
    {
        $e = new AuthenticationException;
        $this->assertStringContainsString('credentials', $e->getMessage());
    }
}
