<?php

namespace Aghfatehi\Msegat\Tests\Unit;

use Aghfatehi\Msegat\DTOs\BalanceResponse;
use Aghfatehi\Msegat\DTOs\OtpResponse;
use Aghfatehi\Msegat\DTOs\SmsResponse;
use PHPUnit\Framework\TestCase;

class DtoTest extends TestCase
{
    public function test_sms_response_from_api(): void
    {
        $response = SmsResponse::fromApiResponse(['code' => '1', 'message' => 'Success']);

        $this->assertTrue($response->successful);
        $this->assertSame('1', $response->code);
        $this->assertNull($response->bulkId);
    }

    public function test_sms_response_with_bulk_id(): void
    {
        $response = SmsResponse::fromApiResponse(['code' => '1-ABC123', 'message' => 'Success'], true);

        $this->assertTrue($response->successful);
        $this->assertSame('1', $response->code);
        $this->assertSame('ABC123', $response->bulkId);
    }

    public function test_sms_response_failed(): void
    {
        $response = SmsResponse::fromApiResponse(['code' => '1060', 'message' => 'Balance is not enough']);

        $this->assertFalse($response->successful);
        $this->assertSame('1060', $response->code);
    }

    public function test_otp_response_success(): void
    {
        $response = OtpResponse::fromApiResponse(['code' => '1', 'id' => 'otp_123']);

        $this->assertTrue($response->successful);
        $this->assertSame('sent', $response->status);
        $this->assertSame('otp_123', $response->otpId);
    }

    public function test_otp_response_failed(): void
    {
        $response = OtpResponse::fromApiResponse(['code' => '1060']);

        $this->assertFalse($response->successful);
        $this->assertSame('failed', $response->status);
    }

    public function test_balance_response_success(): void
    {
        $response = BalanceResponse::fromRawResponse('3042.00');

        $this->assertTrue($response->successful);
        $this->assertSame(3042.0, $response->balance);
    }

    public function test_balance_response_failed(): void
    {
        $response = BalanceResponse::fromRawResponse('M0002');

        $this->assertFalse($response->successful);
        $this->assertSame(0.0, $response->balance);
    }
}
