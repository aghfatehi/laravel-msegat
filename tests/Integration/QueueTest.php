<?php

namespace Aghfatehi\Msegat\Tests\Integration;

use Aghfatehi\Msegat\Facades\Msegat;
use Aghfatehi\Msegat\Jobs\SendSmsJob;
use Aghfatehi\Msegat\MsegatClient;
use Aghfatehi\Msegat\Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

class QueueTest extends TestCase
{
    public function test_sms_job_dispatched(): void
    {
        Bus::fake();

        Http::fake([
            'https://www.msegat.com/gw/sendsms.php' => Http::response([
                'code' => '1',
                'message' => 'Success',
            ], 200),
        ]);

        Msegat::sms()
            ->to('966512345678')
            ->message('Queued message')
            ->queue();

        Bus::assertDispatched(SendSmsJob::class);
    }

    public function test_sms_job_handles_successful_response(): void
    {
        Http::fake([
            'https://www.msegat.com/gw/sendsms.php' => Http::response([
                'code' => '1',
                'message' => 'Success',
            ], 200),
        ]);

        $job = new SendSmsJob([
            'numbers' => '966512345678',
            'userSender' => 'TestSender',
            'msg' => 'Test from job',
            'msgEncoding' => 'UTF8',
            'reqBulkId' => false,
            'reqFilter' => true,
        ]);

        $job->handle(app(MsegatClient::class));

        $this->expectNotToPerformAssertions();
    }
}
