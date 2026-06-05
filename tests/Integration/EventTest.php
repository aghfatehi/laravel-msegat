<?php

namespace Aghfatehi\Msegat\Tests\Integration;

use Aghfatehi\Msegat\Events\MessageSending;
use Aghfatehi\Msegat\Events\MessageSent;
use Aghfatehi\Msegat\Events\OtpGenerated;
use Aghfatehi\Msegat\Events\OtpVerified;
use Aghfatehi\Msegat\Facades\Msegat;
use Aghfatehi\Msegat\MsegatClient;
use Aghfatehi\Msegat\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

class EventTest extends TestCase
{
    private MockInterface $clientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->mock(MsegatClient::class);
        Msegat::setClient($this->clientMock);
    }

    public function test_message_sending_event_dispatched(): void
    {
        Event::fake();

        $this->clientMock->shouldReceive('send')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Success',
            ]);

        Msegat::sms()
            ->to('966512345678')
            ->message('Test event')
            ->send();

        Event::assertDispatched(MessageSending::class);
        Event::assertDispatched(MessageSent::class);
    }

    public function test_otp_events_dispatched(): void
    {
        Event::fake();

        $this->clientMock->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'code' => '1',
                'id' => 'otp_123',
            ]);

        $this->clientMock->shouldReceive('verifyOtp')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Verified',
            ]);

        Msegat::otp()->to('966512345678')->sendOtp();

        Event::assertDispatched(OtpGenerated::class);

        Msegat::otp()->to('966512345678')->verifyOtp('123456');

        Event::assertDispatched(OtpVerified::class);
    }
}
