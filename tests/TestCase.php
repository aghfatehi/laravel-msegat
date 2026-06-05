<?php

namespace Aghfatehi\Msegat\Tests;

use Aghfatehi\Msegat\Facades\Msegat;
use Aghfatehi\Msegat\MsegatServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MsegatServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Msegat' => Msegat::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('msegat.username', 'test_user');
        $app['config']->set('msegat.api_key', 'test_key');
        $app['config']->set('msegat.sender', 'TestSender');
        $app['config']->set('msegat.logging.enabled', false);
        $app['config']->set('msegat.base_url', 'https://www.msegat.com/gw/');
        $app['config']->set('msegat.http_client.max_retries', 0);
    }
}
