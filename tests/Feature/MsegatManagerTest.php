<?php

namespace Aghfatehi\Msegat\Tests\Feature;

use Aghfatehi\Msegat\Exceptions\ValidationException;
use Aghfatehi\Msegat\Facades\Msegat;
use Aghfatehi\Msegat\MsegatClient;
use Aghfatehi\Msegat\Tests\TestCase;
use Mockery\MockInterface;

class MsegatManagerTest extends TestCase
{
    private MockInterface $clientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->mock(MsegatClient::class);
        Msegat::setClient($this->clientMock);
    }

    public function test_sms_send_validates_numbers(): void
    {
        $this->expectException(ValidationException::class);

        Msegat::sms()
            ->message('Hello')
            ->send();
    }

    public function test_sms_send_validates_message(): void
    {
        $this->expectException(ValidationException::class);

        Msegat::sms()
            ->to('966512345678')
            ->send();
    }

    public function test_sms_send_makes_http_call(): void
    {
        $this->clientMock->shouldReceive('send')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Success',
            ]);

        $response = Msegat::sms()
            ->to('966512345678')
            ->message('Hello World')
            ->send();

        $this->assertTrue($response->successful);
        $this->assertSame('1', $response->code);
    }

    public function test_sms_send_with_bulk_id(): void
    {
        $this->clientMock->shouldReceive('send')
            ->once()
            ->andReturn([
                'code' => '1-BULK123',
                'message' => 'Success',
            ]);

        $response = Msegat::sms()
            ->to(['966512345678', '966598765432'])
            ->message('Bulk message')
            ->options(['reqBulkId' => true])
            ->send();

        $this->assertTrue($response->successful);
        $this->assertSame('BULK123', $response->bulkId);
    }

    public function test_sms_send_multiple_numbers(): void
    {
        $this->clientMock->shouldReceive('send')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Success',
            ]);

        $response = Msegat::sms()
            ->to(['0512345678', '0598765432'])
            ->message('Hello all')
            ->send();

        $this->assertTrue($response->successful);
    }

    public function test_otp_send(): void
    {
        $this->clientMock->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'code' => '1',
                'id' => 'otp_test_123',
            ]);

        $response = Msegat::otp()
            ->to('966512345678')
            ->sendOtp();

        $this->assertTrue($response->successful);
        $this->assertSame('otp_test_123', $response->otpId);
    }

    public function test_otp_verify(): void
    {
        $this->clientMock->shouldReceive('verifyOtp')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Verified',
            ]);

        $response = Msegat::otp()
            ->to('966512345678')
            ->verifyOtp('123456');

        $this->assertTrue($response->successful);
    }

    public function test_get_balance(): void
    {
        $this->clientMock->shouldReceive('getBalance')
            ->once()
            ->andReturn('3042.00');

        $balance = Msegat::getBalance();

        $this->assertTrue($balance->successful);
        $this->assertSame(3042.0, $balance->balance);
    }

    public function test_calculate_cost(): void
    {
        $this->clientMock->shouldReceive('calculateCost')
            ->once()
            ->andReturn('3,9');

        $cost = Msegat::sms()
            ->to(['966512345678', '966598765432'])
            ->message('Test message')
            ->calculateCost();

        $this->assertSame(3.9, $cost);
    }

    public function test_custom_sender(): void
    {
        $this->clientMock->shouldReceive('send')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Success',
            ]);

        $response = Msegat::sms()
            ->sender('CustomAD')
            ->to('966512345678')
            ->message('Custom sender test')
            ->send();

        $this->assertTrue($response->successful);
    }

    public function test_scheduled_message(): void
    {
        $this->clientMock->shouldReceive('send')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Success',
            ]);

        $response = Msegat::sms()
            ->to('966512345678')
            ->message('Scheduled message')
            ->at('2026-12-01 10:00:00')
            ->send();

        $this->assertTrue($response->successful);
    }

    public function test_send_whatsapp_fallback(): void
    {
        $response = Msegat::whatsapp()
            ->to('966512345678')
            ->template('welcome')
            ->variables(['name' => 'Ahmed'])
            ->send();

        $this->assertFalse($response->successful);
        $this->assertSame('M0099', $response->code);
    }
}
