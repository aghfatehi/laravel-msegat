<?php

return [

    'username' => env('MSEGAT_USERNAME'),

    'api_key' => env('MSEGAT_API_KEY'),

    'sender' => env('MSEGAT_SENDER', ''),

    'base_url' => env('MSEGAT_BASE_URL', 'https://www.msegat.com/gw/'),

    'timeout' => env('MSEGAT_TIMEOUT', 30),

    'retries' => env('MSEGAT_RETRIES', 3),

    'retry_delay' => env('MSEGAT_RETRY_DELAY', 100),

    'verify_ssl' => env('MSEGAT_VERIFY_SSL', true),

    'queue' => [
        'enabled' => env('MSEGAT_QUEUE_ENABLED', false),
        'connection' => env('MSEGAT_QUEUE_CONNECTION', 'default'),
        'queue' => env('MSEGAT_QUEUE_NAME', 'default'),
    ],

    'logging' => [
        'enabled' => env('MSEGAT_LOGGING_ENABLED', true),
        'channel' => env('MSEGAT_LOG_CHANNEL', 'stack'),
        'level' => env('MSEGAT_LOG_LEVEL', 'info'),
    ],

    'http_client' => [
        'max_retries' => (int) env('MSEGAT_RETRIES', 3),
        'retry_delay' => (int) env('MSEGAT_RETRY_DELAY', 100),
        'timeout' => (int) env('MSEGAT_TIMEOUT', 30),
        'connect_timeout' => (int) env('MSEGAT_CONNECT_TIMEOUT', 10),
    ],

    'webhook' => [
        'secret' => env('MSEGAT_WEBHOOK_SECRET', ''),
        'tolerance' => env('MSEGAT_WEBHOOK_TOLERANCE', 300),
    ],

    'otp' => [
        'code_length' => (int) env('MSEGAT_OTP_CODE_LENGTH', 6),
        'code_ttl' => (int) env('MSEGAT_OTP_CODE_TTL', 300),
        'max_attempts' => (int) env('MSEGAT_OTP_MAX_ATTEMPTS', 5),
        'max_requests_per_minute' => (int) env('MSEGAT_OTP_RATE_LIMIT', 3),
    ],

    'whatsapp' => [
        'email' => env('MSEGAT_WHATSAPP_EMAIL'),
        'password' => env('MSEGAT_WHATSAPP_PASSWORD'),
        'base_url' => env('MSEGAT_WHATSAPP_BASE_URL', 'https://communicateapi.t2.sa/api'),
        'token_cache_ttl' => (int) env('MSEGAT_WHATSAPP_TOKEN_CACHE_TTL', 3300),
        'timeout' => (int) env('MSEGAT_WHATSAPP_TIMEOUT', 30),
    ],

    'endpoints' => [
        'send' => 'sendsms.php',
        'send_personalized' => 'sendVars.php',
        'send_otp' => 'sendOTPCode.php',
        'verify_otp' => 'verifyOTPCode.php',
        'add_sender' => 'addSender.php',
        'get_senders' => 'senders.php',
        'get_messages' => 'getMessages.php',
        'calculate_cost' => 'calculateCost.php',
        'balance' => 'Credits.php',
    ],

];
