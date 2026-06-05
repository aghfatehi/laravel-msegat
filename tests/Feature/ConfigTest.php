<?php

namespace Aghfatehi\Msegat\Tests\Feature;

use Aghfatehi\Msegat\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_config_has_default_values(): void
    {
        $this->assertSame('test_user', config('msegat.username'));
        $this->assertSame('test_key', config('msegat.api_key'));
        $this->assertSame('TestSender', config('msegat.sender'));
        $this->assertSame('https://www.msegat.com/gw/', config('msegat.base_url'));
    }

    public function test_config_endpoints_exist(): void
    {
        $endpoints = config('msegat.endpoints');
        $this->assertIsArray($endpoints);
        $this->assertArrayHasKey('send', $endpoints);
        $this->assertArrayHasKey('send_otp', $endpoints);
        $this->assertArrayHasKey('verify_otp', $endpoints);
        $this->assertArrayHasKey('balance', $endpoints);
        $this->assertSame('sendsms.php', $endpoints['send']);
        $this->assertSame('Credits.php', $endpoints['balance']);
    }

    public function test_otp_config_defaults(): void
    {
        $this->assertSame(6, config('msegat.otp.code_length'));
        $this->assertSame(300, config('msegat.otp.code_ttl'));
        $this->assertSame(5, config('msegat.otp.max_attempts'));
    }
}
