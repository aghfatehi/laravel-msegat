<?php

namespace Aghfatehi\Msegat\Tests\Integration;

use Aghfatehi\Msegat\Events\MessageSending;
use Aghfatehi\Msegat\Events\MessageSent;
use Aghfatehi\Msegat\Events\OtpGenerated;
use Aghfatehi\Msegat\Events\OtpVerified;
use Aghfatehi\Msegat\Facades\Msegat;
use Aghfatehi\Msegat\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class EventTest extends TestCase
{
    public function test_message_sending_event_dispatched(): void
    {
        Event::fake();

        Http::fake([
            'https://www.msegat.com/gw/sendsms.php' => Http::response([
                'code' => '1',
                'message' => 'Success',
            ], 200),
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

        Http::fake([
            'https://www.msegat.com/gw/sendOTPCode.php' => Http::response([
                'code' => '1',
                'id' => 'otp_123',
            ], 200),
            'https://www.msegat.com/gw/verifyOTPCode.php' => Http::response([
                'code' => '1',
                'message' => 'Verified',
            ], 200),
        ]);

        Msegat::otp()->to('966512345678')->sendOtp();
        Msegat::otp()->to('966512345678')->verifyOtp('123456');

        Event::assertDispatched(OtpGenerated::class);
        Event::assertDispatched(OtpVerified::class);
    }
}
