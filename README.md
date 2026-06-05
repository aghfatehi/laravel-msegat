<p align="center">
    <a href="https://www.php.net/"><img src="https://img.shields.io/badge/php-8.2|8.3|8.4-8892BF.svg?style=for-the-badge&logo=php" alt="PHP Version"></a>
    <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10|11|12-FF2D20.svg?style=for-the-badge&logo=laravel" alt="Laravel Version"></a>
    <a href="https://msegat.com"><img src="https://img.shields.io/badge/Msegat-SMS_OTP_WhatsApp-00A859.svg?style=for-the-badge" alt="Msegat"></a>
    <a href="https://github.com/aghfatehi/laravel-msegat/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=for-the-badge" alt="License"></a>
    <a href="https://github.com/aghfatehi/laravel-msegat/actions/workflows/ci.yml"><img src="https://img.shields.io/github/actions/workflow/status/aghfatehi/laravel-msegat/ci.yml?style=for-the-badge&label=CI&logo=github" alt="CI"></a>
    <a href="https://packagist.org/packages/aghfatehi/laravel-msegat"><img src="https://img.shields.io/packagist/v/aghfatehi/laravel-msegat.svg?style=for-the-badge&logo=packagist" alt="Packagist"></a>
    <a href="https://packagist.org/packages/aghfatehi/laravel-msegat"><img src="https://img.shields.io/packagist/dt/aghfatehi/laravel-msegat.svg?style=for-the-badge" alt="Downloads"></a>
</p>

<h1 align="center">Laravel Msegat</h1>
<h3 align="center">SMS, OTP, and WhatsApp integration for the Msegat gateway</h3>

<p align="center">
    <strong>Production-ready for SaaS, enterprise, and open-source projects. Supports Laravel 10, 11, and 12 with PHP 8.2+.</strong>
</p>

---

## Installation

```bash
composer require aghfatehi/laravel-msegat
```

Laravel auto-discovery registers the service provider and facade automatically.

### Publish Configuration

```bash
php artisan vendor:publish --provider="Aghfatehi\Msegat\MsegatServiceProvider" --tag=msegat-config
```

### Publish Migrations

```bash
php artisan vendor:publish --provider="Aghfatehi\Msegat\MsegatServiceProvider" --tag=msegat-migrations
php artisan migrate
```

## Configuration

Add to your `.env`:

```env
MSEGAT_USERNAME=your_username
MSEGAT_API_KEY=your_api_key
MSEGAT_SENDER=YourSender
MSEGAT_BASE_URL=https://www.msegat.com/gw/
MSEGAT_TIMEOUT=30
MSEGAT_RETRIES=3
MSEGAT_VERIFY_SSL=true
MSEGAT_QUEUE_ENABLED=false
MSEGAT_LOGGING_ENABLED=true
MSEGAT_WEBHOOK_SECRET=your_webhook_secret
```

### Config Options

| Variable | Default | Description |
|----------|---------|-------------|
| `MSEGAT_USERNAME` | — | Msegat account username |
| `MSEGAT_API_KEY` | — | Msegat API key |
| `MSEGAT_SENDER` | `''` | Default sender name |
| `MSEGAT_BASE_URL` | `https://www.msegat.com/gw/` | API base URL |
| `MSEGAT_TIMEOUT` | `30` | HTTP request timeout (seconds) |
| `MSEGAT_RETRIES` | `3` | Max retry attempts on failure |
| `MSEGAT_VERIFY_SSL` | `true` | Verify SSL certificate |
| `MSEGAT_QUEUE_ENABLED` | `false` | Enable async webhook processing |
| `MSEGAT_LOGGING_ENABLED` | `true` | Enable request/response logging |
| `MSEGAT_WEBHOOK_SECRET` | `''` | Secret for webhook signature verification |

---

## Usage

### SMS

```php
use Aghfatehi\Msegat\Facades\Msegat;

// Single recipient
$response = Msegat::sms()
    ->to('966512345678')
    ->message('Hello World')
    ->send();

// Multiple recipients
$response = Msegat::sms()
    ->to(['966512345678', '966598765432'])
    ->message('Bulk message')
    ->send();

// Custom sender
$response = Msegat::sms()
    ->sender('CustomAD')
    ->to('966512345678')
    ->message('Custom sender test')
    ->send();

// Scheduled message
$response = Msegat::sms()
    ->to('966512345678')
    ->message('Scheduled message')
    ->at('2026-12-01 10:00:00')
    ->send();

// Get bulk ID
$response = Msegat::sms()
    ->to(['966512345678', '966598765432'])
    ->message('Get bulk ID')
    ->options(['reqBulkId' => true])
    ->send();

$bulkId = $response->bulkId;
```

### Personalized Messages

```php
$response = Msegat::sms()
    ->to(['966512345678', '966598765432'])
    ->message('Hello {name}, your order {order} is ready')
    ->sendPersonalized([
        ['name' => 'Ahmed', 'order' => '1001'],
        ['name' => 'Mohammed', 'order' => '1002'],
    ]);
```

### OTP

```php
// Send OTP
$otpResponse = Msegat::otp()
    ->to('966512345678')
    ->sendOtp();

$otpId = $otpResponse->otpId;

// Verify OTP
$verification = Msegat::otp()
    ->to('966512345678')
    ->verifyOtp('123456');

if ($verification->successful) {
    // OTP verified
}
```

### WhatsApp (Abstract)

WhatsApp endpoints are not yet publicly documented by Msegat. Calling `send()` on WhatsApp mode returns a graceful fallback response.

```php
$response = Msegat::whatsapp()
    ->to('966512345678')
    ->template('welcome')
    ->variables(['name' => 'Ahmed'])
    ->send();
// Returns unsuccessful response with informative message
```

### Balance

```php
$balance = Msegat::getBalance();
$credits = $balance->balance; // float
```

### Message Cost Calculation

```php
$cost = Msegat::sms()
    ->to(['966512345678', '966598765432'])
    ->message('Test message')
    ->calculateCost();
// Returns float
```

### Sender Management

```php
// List senders
$senders = Msegat::getSenders();

// Add sender
// (available via direct API call - endpoint: addSender.php)
```

### Retrieve Messages

```php
$messages = Msegat::forBulkId('BULK123')
    ->getMessages();

$messages = Msegat::forBulkId('BULK123')
    ->page(2)
    ->limit(10)
    ->getMessages();
```

### Test Message

```php
Msegat::sms()
    ->to('966512345678')
    ->sendTestMessage();
```

---

## Queue Support

```php
// Dispatch to default queue
Msegat::sms()
    ->to('966512345678')
    ->message('Queued message')
    ->queue();

// Custom queue connection
Msegat::sms()
    ->to('966512345678')
    ->message('Queued message')
    ->queue('redis', 'high');
```

The `SendSmsJob` retries up to 3 times with a 5-second backoff.

---

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `MessageSending` | `$data` (array) | Before sending SMS |
| `MessageSent` | `$response` (SmsResponse) | After SMS sent |
| `OtpGenerated` | `$number`, `$response` (OtpResponse) | After OTP sent |
| `OtpVerified` | `$number`, `$code`, `$response` (OtpResponse) | After OTP verified |
| `WebhookReceived` | `$type`, `$payload` (array) | When webhook is received |

Example listener registration in `EventServiceProvider`:

```php
protected $listen = [
    \Aghfatehi\Msegat\Events\MessageSent::class => [
        \App\Listeners\LogMsegatMessage::class,
    ],
];
```

---

## Webhooks

The package registers four webhook routes under the `/webhook/msegat/` prefix:

| Route | Purpose |
|-------|---------|
| `POST /webhook/msegat/delivery` | Delivery reports |
| `POST /webhook/msegat/status` | Message status updates |
| `POST /webhook/msegat/incoming` | Incoming messages |
| `POST /webhook/msegat/failed` | Failed messages |

### Signature Verification

Set `MSEGAT_WEBHOOK_SECRET` in `.env` and each webhook request must include:

- `X-Msegat-Signature`: HMAC-SHA256 of `timestamp.payload`
- `X-Msegat-Timestamp`: Unix timestamp

Requests outside a 5-minute tolerance window are rejected.

---

## Notification Channel

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class WelcomeSms extends Notification
{
    public function via($notifiable): array
    {
        return ['msegat'];
    }

    public function toMsegat($notifiable): string
    {
        return "Welcome {$notifiable->name} to our platform!";
    }
}
```

Add `routeNotificationForMsegat()` to your notifiable model:

```php
public function routeNotificationForMsegat(): string
{
    return $this->phone;
}
```

---

## Artisan Commands

```bash
# Check account balance
php artisan msegat:balance

# List registered senders
php artisan msegat:senders
```

---

## Testing

```bash
composer test
```

With coverage:

```bash
composer test-coverage
```

The test suite includes:
- **Unit Tests**: DTOs, enums, exceptions, phone formatter
- **Feature Tests**: SMS sending, OTP, balance, webhooks, config
- **Integration Tests**: Events, queues

---

## Code Quality

```bash
# Laravel Pint (PSR-12)
composer lint

# Auto-fix
composer lint-fix

# PHPStan (level 6)
composer analyse

# Rector
composer rector
```

---

## Database Migrations

| Table | Purpose |
|-------|---------|
| `msegat_sms_logs` | SMS send history |
| `msegat_otp_requests` | OTP request tracking |
| `msegat_delivery_reports` | Delivery report storage |
| `msegat_webhook_logs` | Webhook event log |
| `msegat_failed_requests` | Failed request records |

---

## Status Codes and Messages

### Success

| Code | Message |
|------|---------|
| `M0000` | Success |

### Errors

| Code | Message |
|------|---------|
| `M0001` | Variables missing |
| `M0002` | Invalid login info |
| `M0022` | Exceed number of senders allowed |
| `M0023` | Sender Name is active or under activation or refused |
| `M0024` | Sender Name should be in English or number |
| `M0025` | Invalid Sender Name Length |
| `M0026` | Sender Name is already activated or not found |
| `M0027` | Activation Code is not Correct |
| `1010` | Variables missing |
| `1020` | Invalid login info |
| `1050` | MSG body is empty |
| `1060` | Balance is not enough |
| `1061` | MSG duplicated |
| `1064` | Free OTP, Invalid MSG content you should use "Pin Code is: xxxx", "Verification Code: xxxx" or upgrade your account and activate your sender to send any content |
| `1110` | Sender name is missing or incorrect |
| `1120` | Mobile numbers is not correct |
| `1140` | MSG length is too long |
| `M0029` | Invalid Sender Name - Sender Name should contain only letters, numbers and the maximum length should be 11 characters |
| `M0030` | Sender Name should ended with AD |
| `M0031` | Maximum allowed size of uploaded file is 5 MB |
| `M0032` | Only pdf, png, jpg and jpeg files are allowed! |
| `M0033` | Sender Type should be normal or whitelist only |
| `M0034` | Please Use POST Method |
| `M0036` | There is no any sender |

---

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

---

## Security

If you discover any security-related issues, please email instead of using the issue tracker.

---

## Contributing

Contributions are welcome! Please follow these steps to contribute:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/new-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/new-feature`).
5. Create a new Pull Request.

---

## Support

If you have any questions or issues, feel free to open an issue on the [GitHub repository](https://github.com/aghfatehi/laravel-msegat/issues) or contact the author.

---

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
