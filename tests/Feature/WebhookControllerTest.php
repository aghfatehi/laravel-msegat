<?php

namespace Aghfatehi\Msegat\Tests\Feature;

use Aghfatehi\Msegat\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class WebhookControllerTest extends TestCase
{
    private string $secret = 'test_secret_key_123';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('msegat.webhook.secret', $this->secret);
        Config::set('msegat.webhook.tolerance', 300);
        Config::set('msegat.logging.enabled', false);
    }

    public function test_delivery_report_webhook(): void
    {
        $payload = ['messageId' => 'msg_123', 'status' => 'delivered', 'number' => '966512345678'];
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.json_encode($payload), $this->secret);

        $response = $this->postJson('/webhook/msegat/delivery', $payload, [
            'X-Msegat-Signature' => $signature,
            'X-Msegat-Timestamp' => $timestamp,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_status_webhook(): void
    {
        $payload = ['messageId' => 'msg_456', 'status' => 'sent'];
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.json_encode($payload), $this->secret);

        $response = $this->postJson('/webhook/msegat/status', $payload, [
            'X-Msegat-Signature' => $signature,
            'X-Msegat-Timestamp' => $timestamp,
        ]);

        $response->assertOk();
    }

    public function test_incoming_message_webhook(): void
    {
        $payload = ['from' => '966512345678', 'message' => 'Hello'];
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.json_encode($payload), $this->secret);

        $response = $this->postJson('/webhook/msegat/incoming', $payload, [
            'X-Msegat-Signature' => $signature,
            'X-Msegat-Timestamp' => $timestamp,
        ]);

        $response->assertOk();
    }

    public function test_failed_message_webhook(): void
    {
        $payload = ['messageId' => 'msg_789', 'error' => 'Network error'];
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.json_encode($payload), $this->secret);

        $response = $this->postJson('/webhook/msegat/failed', $payload, [
            'X-Msegat-Signature' => $signature,
            'X-Msegat-Timestamp' => $timestamp,
        ]);

        $response->assertOk();
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = ['messageId' => 'msg_123'];

        $response = $this->postJson('/webhook/msegat/delivery', $payload, [
            'X-Msegat-Signature' => 'invalid_signature',
            'X-Msegat-Timestamp' => (string) time(),
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_rejects_missing_signature(): void
    {
        $payload = ['messageId' => 'msg_123'];

        $response = $this->postJson('/webhook/msegat/delivery', $payload);

        $response->assertStatus(401);
    }

    public function test_webhook_rejects_expired_timestamp(): void
    {
        $payload = ['messageId' => 'msg_123'];
        $oldTimestamp = (string) (time() - 3600);
        $signature = hash_hmac('sha256', $oldTimestamp.'.'.json_encode($payload), $this->secret);

        $response = $this->postJson('/webhook/msegat/delivery', $payload, [
            'X-Msegat-Signature' => $signature,
            'X-Msegat-Timestamp' => $oldTimestamp,
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_works_without_secret_configured(): void
    {
        Config::set('msegat.webhook.secret', '');

        $payload = ['messageId' => 'msg_123'];

        $response = $this->postJson('/webhook/msegat/delivery', $payload);
        $response->assertOk();
    }
}
