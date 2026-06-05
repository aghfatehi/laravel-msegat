<?php

namespace Aghfatehi\Msegat\Tests\Integration;

use Aghfatehi\Msegat\Facades\Msegat;
use Aghfatehi\Msegat\Jobs\SendSmsJob;
use Aghfatehi\Msegat\MsegatClient;
use Aghfatehi\Msegat\Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;

class QueueTest extends TestCase
{
    private MockInterface $clientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->mock(MsegatClient::class);
        Msegat::setClient($this->clientMock);
    }

    public function test_sms_job_dispatched(): void
    {
        Bus::fake();

        Msegat::sms()
            ->to('966512345678')
            ->message('Queued message')
            ->queue();

        Bus::assertDispatched(SendSmsJob::class);
    }

    public function test_sms_job_handles_successful_response(): void
    {
        $this->clientMock->shouldReceive('send')
            ->once()
            ->andReturn([
                'code' => '1',
                'message' => 'Success',
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
    }
}
