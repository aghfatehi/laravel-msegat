<?php

namespace Aghfatehi\Msegat;

use Aghfatehi\Msegat\Enums\ApiEndpoint;
use Aghfatehi\Msegat\Exceptions\ApiException;
use Aghfatehi\Msegat\Exceptions\MsegatException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MsegatClient
{
    private PendingRequest $http;

    private array $credentials;

    public function __construct()
    {
        $config = config('msegat');

        $this->credentials = [
            'userName' => $config['username'],
            'apiKey' => $config['api_key'],
        ];

        $this->http = Http::baseUrl($config['base_url'])
            ->timeout($config['http_client']['timeout'])
            ->connectTimeout($config['http_client']['connect_timeout'])
            ->withOptions([
                'verify' => $config['verify_ssl'],
            ])
            ->retry(
                $config['http_client']['max_retries'],
                $config['http_client']['retry_delay'],
                fn ($exception) => $exception instanceof RequestException,
            )
            ->asMultipart();
    }

    public function send(array $data): array
    {
        return $this->request(ApiEndpoint::Send, $data);
    }

    public function sendPersonalized(array $data): array
    {
        return $this->request(ApiEndpoint::SendPersonalized, $data);
    }

    public function sendOtp(array $data): array
    {
        return $this->request(ApiEndpoint::SendOtp, $data);
    }

    public function verifyOtp(array $data): array
    {
        return $this->request(ApiEndpoint::VerifyOtp, $data);
    }

    public function getBalance(): string
    {
        $payload = $this->buildMultipart(ApiEndpoint::Balance, []);

        $response = $this->http->post(
            config('msegat.endpoints.balance'),
            $payload
        );

        $this->logRequest('POST', config('msegat.endpoints.balance'), $response->status());

        $body = $response->body();

        if (in_array($body, ['M0002', '1020', 'M0001', '1010'], true)) {
            throw new ApiException($body, "Balance inquiry failed: {$body}");
        }

        return $body;
    }

    public function getSenders(): array
    {
        return $this->request(ApiEndpoint::GetSenders, []);
    }

    public function getMessages(array $filters): array
    {
        return $this->request(ApiEndpoint::GetMessages, $filters);
    }

    public function calculateCost(array $data): string
    {
        $payload = $this->buildMultipart(ApiEndpoint::CalculateCost, $data);

        $response = $this->http->post(
            config('msegat.endpoints.calculate_cost'),
            $payload
        );

        $this->logRequest('POST', config('msegat.endpoints.calculate_cost'), $response->status());

        return $response->body();
    }

    private function request(ApiEndpoint $endpoint, array $data): array
    {
        $path = $endpoint->path();

        $payload = $this->buildMultipart($endpoint, $data);

        $response = $this->http->post($path, $payload);

        $this->logRequest('POST', $path, $response->status());

        $body = $response->body();

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MsegatException("Invalid JSON response from Msegat API: {$body}");
        }

        return $decoded;
    }

    private function buildMultipart(ApiEndpoint $endpoint, array $data): array
    {
        $payload = [];

        foreach ($this->credentials as $key => $value) {
            $payload[] = [
                'name' => $key,
                'contents' => $value,
            ];
        }

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $payload[] = [
                'name' => $key,
                'contents' => (string) $value,
            ];
        }

        return $payload;
    }

    private function logRequest(string $method, string $path, int $status): void
    {
        if (! config('msegat.logging.enabled')) {
            return;
        }

        Log::channel(config('msegat.logging.channel'))
            ->log(config('msegat.logging.level'), "Msegat API: {$method} {$path} responded {$status}");
    }
}
