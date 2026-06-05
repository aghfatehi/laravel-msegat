<?php

namespace Aghfatehi\Msegat;

use Aghfatehi\Msegat\Enums\ApiEndpoint;
use Aghfatehi\Msegat\Exceptions\ApiException;
use Aghfatehi\Msegat\Exceptions\MsegatException;
use Illuminate\Support\Facades\Log;

class MsegatClient
{
    private array $credentials;

    private array $config;

    public function __construct()
    {
        $config = config('msegat');

        $this->credentials = [
            'userName' => $config['username'],
            'apiKey' => $config['api_key'],
        ];

        $this->config = $config;
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

        $body = $this->curlPost(
            $this->config['base_url'].$this->config['endpoints']['balance'],
            $payload
        );

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

        return $this->curlPost(
            $this->config['base_url'].$this->config['endpoints']['calculate_cost'],
            $payload
        );
    }

    private function request(ApiEndpoint $endpoint, array $data): array
    {
        $url = $this->config['base_url'].$endpoint->path();

        $payload = $this->buildMultipart($endpoint, $data);

        $body = $this->curlPost($url, $payload);

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

    private function curlPost(string $url, array $multipartData): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['http_client']['timeout']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config['http_client']['connect_timeout']);

        if (! $this->config['verify_ssl']) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $postFields = [];
        foreach ($multipartData as $part) {
            $postFields[$part['name']] = $part['contents'];
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        $this->logRequest('POST', $url, $httpCode);

        if ($error) {
            throw new MsegatException("cURL error: {$error}");
        }

        return $response;
    }

    private function logRequest(string $method, string $path, int $status): void
    {
        if ($this->config['logging']['enabled'] ?? false) {
            Log::channel($this->config['logging']['channel'])
                ->log($this->config['logging']['level'], "Msegat API: {$method} {$path} responded {$status}");
        }
    }
}
