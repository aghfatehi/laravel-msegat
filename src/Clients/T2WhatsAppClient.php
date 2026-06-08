<?php

namespace Aghfatehi\Msegat\Clients;

use Aghfatehi\Msegat\Exceptions\AuthenticationException;
use Aghfatehi\Msegat\Exceptions\MsegatException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Low-level HTTP client for the T2 Communicate WhatsApp Business API.
 *
 * Handles JWT Bearer-token authentication, JSON request/response,
 * and token caching via Laravel Cache. All public methods map to
 * T2 WhatsApp API endpoints (base: https://communicateapi.t2.sa/api).
 *
 * @see https://t2techdetails.docs.apiary.io/
 */
class T2WhatsAppClient
{
    /** @var array<string,mixed> WhatsApp config from msegat.php. */
    private array $config;

    /**
     * Initializes the client with WhatsApp credentials from config/msegat.php.
     */
    public function __construct()
    {
        $this->config = config('msegat.whatsapp', []);
    }

    /**
     * Send a plain text WhatsApp message to a single recipient.
     *
     * @param  string  $recipientNumber  REQUIRED. Phone number in international format (e.g. '201276267641').
     * @param  string  $text  REQUIRED. Message body (max 4096 chars, UTF-8).
     * @return array The decoded JSON response from T2 API.
     *
     * @throws AuthenticationException If authentication fails.
     * @throws MsegatException On cURL or API errors.
     */
    public function sendText(string $recipientNumber, string $text): array
    {
        return $this->authenticatedRequest('POST', '/message/send-custom', [
            'recipient' => ['id' => $recipientNumber],
            'message' => ['text' => $text],
        ]);
    }

    /**
     * Send a WhatsApp template message with variable substitution.
     *
     * Template variables are appended to the template name using the
     * T2 marker syntax: ##template_name##var1##var2##...
     *
     * @param  string  $recipientNumber  REQUIRED. Phone number in international format.
     * @param  string  $templateName  REQUIRED. The approved WhatsApp template name.
     * @param  array<int,string>  $variables  OPTIONAL. Positional variable values for the template.
     * @return array The decoded JSON response from T2 API.
     *
     * @throws AuthenticationException If authentication fails.
     * @throws MsegatException On cURL or API errors.
     */
    public function sendTemplate(string $recipientNumber, string $templateName, array $variables = []): array
    {
        $text = '##'.$templateName.'##'.implode('##', $variables);

        return $this->authenticatedRequest('POST', '/message/send-custom', [
            'recipient' => ['id' => $recipientNumber],
            'message' => ['text' => $text],
        ]);
    }

    /**
     * Send a media message (image, document, audio, or video).
     *
     * @param  string  $recipientNumber  REQUIRED. Phone number in international format.
     * @param  array<string,mixed>  $media  REQUIRED. Media payload with keys:
     *                                      'contentType' (string), 'url' (string),
     *                                      'size' (int), 'name' (string),
     *                                      'mediaType' ('Image'|'File'|'Audio'|'Video').
     * @return array The decoded JSON response from T2 API.
     *
     * @throws AuthenticationException If authentication fails.
     * @throws MsegatException On cURL or API errors.
     */
    public function sendMedia(string $recipientNumber, array $media): array
    {
        return $this->authenticatedRequest('POST', '/message/send-custom', [
            'recipient' => ['id' => $recipientNumber],
            'media' => $media,
        ]);
    }

    /**
     * Authenticate with T2 API and retrieve a JWT Bearer token.
     *
     * The token is cached using the configured TTL (default 3300s = 55 min)
     * since tokens expire in 3600 seconds.
     *
     * @return string The JWT access token.
     *
     * @throws AuthenticationException On invalid credentials or server error.
     */
    public function login(): string
    {
        $url = $this->config['base_url'].'/auth/login';

        $body = $this->curlPostJson($url, [
            'email' => $this->config['email'] ?? '',
            'password' => $this->config['password'] ?? '',
        ]);

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['token'])) {
            throw new AuthenticationException(
                $decoded['Message'] ?? 'T2 WhatsApp authentication failed'
            );
        }

        return $decoded['token'];
    }

    /**
     * Get a valid JWT token, using cached token if available.
     */
    public function getToken(): string
    {
        $cacheKey = 'msegat_whatsapp_token';

        return Cache::remember($cacheKey, $this->config['token_cache_ttl'], function () {
            return $this->login();
        });
    }

    /**
     * Execute an authenticated request, with automatic re-authentication on 401.
     *
     * @param  string  $method  HTTP method (GET, POST, etc.).
     * @param  string  $path  API path (e.g. '/message/send-custom').
     * @param  array<string,mixed>|null  $data  OPTIONAL. Request body for POST/PUT.
     * @param  bool  $isRetry  Internal. Whether this is a retry after 401.
     * @return array The decoded JSON response.
     *
     * @throws AuthenticationException If re-authentication also fails.
     * @throws MsegatException On cURL or API errors.
     */
    private function authenticatedRequest(string $method, string $path, ?array $data = null, bool $isRetry = false): array
    {
        $url = $this->config['base_url'].$path;
        $token = $this->getToken();

        $body = $this->curlPostJson($url, $data, $token);

        $httpCode = $this->lastHttpCode;

        if ($httpCode === 401 && !$isRetry) {
            Cache::forget('msegat_whatsapp_token');

            return $this->authenticatedRequest($method, $path, $data, true);
        }

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MsegatException("Invalid JSON response from T2 WhatsApp API: {$body}");
        }

        if (400 <= $httpCode && isset($decoded['Message'])) {
            throw new MsegatException("T2 WhatsApp API error: {$decoded['Message']}", $httpCode);
        }

        return $decoded;
    }

    /** @var int HTTP status code from the last cURL request. */
    private int $lastHttpCode = 200;

    /**
     * Execute a JSON POST request via cURL.
     *
     * @param  string  $url  The full API URL.
     * @param  array<string,mixed>|null  $data  Request payload (associative array).
     * @param  string|null  $token  OPTIONAL. JWT Bearer token for Authorization header.
     * @return string Raw response body.
     *
     * @throws MsegatException On cURL errors.
     */
    private function curlPostJson(string $url, ?array $data = null, ?string $token = null): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout'] ?? 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $headers = ['Content-Type: application/json', 'Accept: application/json'];

        if ($token) {
            $headers[] = 'Authorization: Bearer '.$token;
        }

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        $this->logRequest('POST', $url, $this->lastHttpCode);

        if ($error) {
            throw new MsegatException("T2 WhatsApp cURL error: {$error}");
        }

        return $response;
    }

    /**
     * Log an API request if logging is enabled.
     *
     * @param  string  $method  HTTP method.
     * @param  string  $path  The request URL.
     * @param  int  $status  HTTP status code.
     */
    private function logRequest(string $method, string $path, int $status): void
    {
        $msegatConfig = config('msegat');

        if ($msegatConfig['logging']['enabled'] ?? false) {
            Log::channel($msegatConfig['logging']['channel'])
                ->log($msegatConfig['logging']['level'], "T2 WhatsApp API: {$method} {$path} responded {$status}");
        }
    }
}
