<?php

namespace Aghfatehi\Msegat\Tests\Unit;

use Aghfatehi\Msegat\Enums\ApiEndpoint;
use Aghfatehi\Msegat\Enums\MessageStatus;
use Aghfatehi\Msegat\Enums\ResponseCode;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function test_api_endpoint_has_path(): void
    {
        $this->assertIsString(ApiEndpoint::Send->path());
        $this->assertIsString(ApiEndpoint::SendOtp->path());
        $this->assertIsString(ApiEndpoint::Balance->path());
    }

    public function test_response_code_success(): void
    {
        $this->assertTrue(ResponseCode::Success->isSuccess());
        $this->assertTrue(ResponseCode::SuccessAlt->isSuccess());
        $this->assertFalse(ResponseCode::InvalidLogin->isSuccess());
    }

    public function test_response_code_messages(): void
    {
        $this->assertSame('Success', ResponseCode::Success->message());
        $this->assertSame('Invalid login info', ResponseCode::InvalidLogin->message());
        $this->assertSame('Balance is not enough', ResponseCode::InsufficientBalance->message());
    }

    public function test_message_status_values(): void
    {
        $this->assertSame('pending', MessageStatus::Pending->value);
        $this->assertSame('delivered', MessageStatus::Delivered->value);
        $this->assertSame('failed', MessageStatus::Failed->value);
    }
}
