# Changelog

All notable changes to `aghfatehi/laravel-msegat` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2026-06-05

### Added

- **SMS Messaging:** Single, bulk, and personalized SMS via fluent API
- **OTP Service:** Send and verify one-time passwords
- **WhatsApp Abstraction:** Driver-based architecture ready for undocumented endpoints
- **Account Balance:** Check remaining SMS credits
- **Message Cost Calculation:** Calculate cost before sending
- **Sender Management:** List registered senders
- **Message Retrieval:** Paginated lookup by bulk ID
- **Scheduled Messages:** Send at a specified future time
- **Queue Support:** Async SMS dispatch via `->queue()` and `SendSmsJob`
- **Event System:** `MessageSending`, `MessageSent`, `OtpGenerated`, `OtpVerified`, `WebhookReceived`
- **Notification Channel:** Native Laravel notification channel (`msegat`)
- **Webhook Handling:** 4 routes (delivery, status, incoming, failed) with HMAC-SHA256 signature verification and replay attack protection
- **Database Migrations:** 5 tables with indexes, foreign keys, soft deletes:
  - `msegat_sms_logs`
  - `msegat_otp_requests`
  - `msegat_delivery_reports`
  - `msegat_webhook_logs`
  - `msegat_failed_requests`
- **Artisan Commands:** `msegat:balance` and `msegat:senders`
- **HTTP Client:** Laravel HTTP with retry logic, timeout handling, multipart form-data
- **Configuration:** Published config with all environment variables
- **Code Quality:** PHPStan level 2, Laravel Pint (PSR-12), Rector
- **CI/CD:** GitHub Actions matrix build (PHP 8.2/8.3/8.4 × Laravel 10/11/12)
- **Testing:** 47 tests (Unit + Feature + Integration) with HTTP fakes and mock API responses
- **Documentation:** README with installation, usage, events, webhooks, queues, troubleshooting

[1.0.0]: https://github.com/aghfatehi/laravel-msegat/releases/tag/v1.0.0
