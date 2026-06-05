<?php

namespace Aghfatehi\Msegat;

use Aghfatehi\Msegat\Enums\ApiEndpoint;
use Aghfatehi\Msegat\Exceptions\ApiException;
use Aghfatehi\Msegat\Exceptions\MsegatException;
use Illuminate\Support\Facades\Log;

/**
 * Low-level HTTP client for the Msegat REST API.
 *
 * Handles authentication, multipart form building, cURL execution,
 * and JSON response parsing. All public methods map to Msegat API endpoints.
 */
class MsegatClient
{
    /** @var array{userName: string, apiKey: string} Msegat API credentials. */
    private array $credentials;

    /** @var array<string,mixed> Full msegat config array. */
    private array $config;

    /**
     * @param  array<string,mixed>|null  $config  OPTIONAL. Config override for testing.
     */
    public function __construct()
    {
        $config = config('msegat');

        $this->credentials = [
            'userName' => $config['username'],
            'apiKey' => $config['api_key'],
        ];

        $this->config = $config;
    }

    /**
     * Send a standard SMS message.
     *
     * @param  array<string,mixed>  $data  REQUIRED. Message payload (numbers, sender, msg, etc.).
     * @return array The decoded JSON response from Msegat.
     */
    public function send(array $data): array
    {
        return $this->request(ApiEndpoint::Send, $data);
    }

    /**
     * Send personalized (variable-based) SMS messages.
     *
     * @param  array<string,mixed>  $data  REQUIRED. Payload including 'vars' per recipient.
     * @return array The decoded JSON response.
     */
    public function sendPersonalized(array $data): array
    {
        return $this->request(ApiEndpoint::SendPersonalized, $data);
    }

    /**
     * Send an OTP code to a single number.
     *
     * @param  array<string,mixed>  $data  REQUIRED. Payload with 'number', 'userSender', 'lang'.
     * @return array The decoded JSON response.
     */
    public function sendOtp(array $data): array
    {
        return $this->request(ApiEndpoint::SendOtp, $data);
    }

    /**
     * Verify an OTP code.
     *
     * @param  array<string,mixed>  $data  REQUIRED. Payload with 'number', 'code', 'lang'.
     * @return array The decoded JSON response.
     */
    public function verifyOtp(array $data): array
    {
        return $this->request(ApiEndpoint::VerifyOtp, $data);
    }

    /**
     * Get account balance (SMS credits).
     *
     * @return string Raw response body (credit count or error code).
     *
     * @throws ApiException On known error codes.
     */
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

    /**
     * Retrieve all registered sender names.
     *
     * @return array The decoded JSON response.
     */
    public function getSenders(): array
    {
        return $this->request(ApiEndpoint::GetSenders, []);
    }

    /**
     * Get delivery reports / message statuses.
     *
     * @param  array<string,mixed>  $filters  REQUIRED. At minimum 'reqBulkId', optionally 'pageNumber', 'limit'.
     * @return array The decoded JSON response.
     */
    public function getMessages(array $filters): array
    {
        return $this->request(ApiEndpoint::GetMessages, $filters);
    }

    /**
     * Calculate the cost of a message before sending.
     *
     * @param  array<string,mixed>  $data  REQUIRED. Payload with contacts, msg, etc.
     * @return string Raw response body (cost string).
     */
    public function calculateCost(array $data): string
    {
        $payload = $this->buildMultipart(ApiEndpoint::CalculateCost, $data);

        return $this->curlPost(
            $this->config['base_url'].$this->config['endpoints']['calculate_cost'],
            $payload
        );
    }

    /**
     * Execute an API request that returns JSON.
     *
     * @param  ApiEndpoint  $endpoint  The API endpoint to call.
     * @param  array<string,mixed>  $data  The request payload.
     * @return array The decoded JSON response.
     *
     * @throws MsegatException On invalid JSON response.
     */
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

    /**
     * Build a Guzzle-style multipart payload array with credentials merged in.
     *
     * @param  ApiEndpoint  $endpoint  The target endpoint (unused in payload, kept for extensibility).
     * @param  array<string,mixed>  $data  The request data fields.
     * @return array<int, array{name: string, contents: string}> Multipart array.
     */
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

    /**
     * Execute a POST request via cURL.
     *
     * @param  string  $url  The full API URL.
     * @param  array<int, array{name: string, contents: string}>  $multipartData  Multipart form data.
     * @return string Raw response body.
     *
     * @throws MsegatException On cURL errors.
     */
    private function curlPost(string $url, array $multipartData): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['http_client']['timeout']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config['http_client']['connect_timeout']);

        if (!$this->config['verify_ssl']) {
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

    /**
     * Log an API request if logging is enabled in config.
     *
     * @param  string  $method  HTTP method (e.g. POST).
     * @param  string  $path  The request URL path.
     * @param  int  $status  HTTP status code.
     * @return void
     */
    private function logRequest(string $method, string $path, int $status): void
    {
        if ($this->config['logging']['enabled'] ?? false) {
            Log::channel($this->config['logging']['channel'])
                ->log($this->config['logging']['level'], "Msegat API: {$method} {$path} responded {$status}");
        }
    }
}
