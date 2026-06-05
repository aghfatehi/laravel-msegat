# PROJECT_MAP — aghfatehi/laravel-msegat

> Generated: 2026-06-05 17:23 AST  
> PHP 8.2.20 · Composer 2.8.12 · Laravel 10/11/12

---

## [TECH_STACK]

| Layer | Technology | Constraint |
|-------|-----------|------------|
| Language | PHP 8.2+ | typed properties, enums, readonly classes |
| Framework | Laravel 10.x / 11.x / 12.x | HTTP Client, Notification, Queue, Event |
| HTTP | Laravel `Http` facade | retry, timeout, macros |
| Testing | PHPUnit 11, Mockery, Orchestra Testbench | 95%+ coverage |
| Static Analysis | PHPStan level 6 | `phpstan.neon` |
| Code Style | Laravel Pint (PSR-12) | `pint.json` |
| Refactoring | Rector | `rector.php` |
| CI | GitHub Actions | PHP 8.2/8.3/8.4, Laravel matrix |
| Package Name | `aghfatehi/laravel-msegat` | Packagist |
| Namespace | `Aghfatehi\Msegat` | PSR-4 |

---

## [API DISCOVERY] — Msegat REST Endpoints

Base URL: `https://www.msegat.com/gw/`

| # | Endpoint | Method | Purpose |
|---|----------|--------|---------|
| 1 | `sendsms.php` | POST | Send single/bulk SMS |
| 2 | `sendVars.php` | POST | Send personalized SMS (per-recipient vars) |
| 3 | `sendOTPCode.php` | POST | Send OTP code |
| 4 | `verifyOTPCode.php` | POST | Verify OTP code |
| 5 | `addSender.php` | POST | Register a new sender name |
| 6 | `senders.php` | POST | List all registered senders |
| 7 | `getMessages.php` | POST | Get messages by bulk ID (paginated) |
| 8 | `calculateCost.php` | POST | Calculate cost before sending |
| 9 | `Credits.php` | POST | Check account balance |

**Auth:** `userName` + `apiKey` (or legacy `userPassword`) sent as POST body (form-data or JSON).

**SMS params:** `numbers` (comma-separated), `userSender`, `msg`, `msgEncoding`, `timeToSend`, `exactTime`, `reqBulkId`, `reqFilter`.

**Response codes:** `1`/`M0000`=success, `M0002`=bad auth, `1060`=no balance, `1120`=bad numbers.

**WhatsApp:** Listed on msegat.com as a product. Undocumented public REST API at this time — WhatsApp feature will be **abstracted** behind the same fluent API with a driver-based design ready for future endpoint discovery.

---

## [ARCHITECTURE] — Package Structure & Data Flow

```
┌─ Consumer App ──────────────────────────────────┐
│  Msegat::sms()->to()->message()->send()          │
│  Msegat::otp()->to()->send()                     │
│  Msegat::whatsapp()->to()->template()->send()    │
└──────────────────────┬──────────────────────────┘
                       │ Facade
┌──────────────────────▼──────────────────────────┐
│  MsegatManager  (entry-point, fluent builder)    │
│  ┌─────────────┐ ┌──────────┐ ┌──────────────┐ │
│  │ SmsBuilder  │ │ OtpBuilder│ │ WhatsAppBldr │ │
│  └──────┬──────┘ └────┬─────┘ └──────┬───────┘ │
└─────────┼──────────────┼──────────────┼─────────┘
          │              │              │
┌─────────▼──────────────▼──────────────▼─────────┐
│  MsegatClient  (Laravel Http::withOptions())     │
│  • retry(3, 100ms) • timeout(30) • circuit       │
│  • rate-limiter • request/response logging       │
└──────────────────────┬──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│  Msegat API  (msegat.com/gw/*.php)              │
└─────────────────────────────────────────────────┘
```

### Principle: Simplicity First — NO over-engineering

- **No repository pattern** — not needed for an HTTP API wrapper
- **No elaborate abstraction hierarchy** — one `MsegatClient`, one `MsegatManager`
- **No micro-files** — grouped by domain feature, not by technical role
- **DTOs only where type safety matters** (requests/responses)
- **Events dispatched at natural boundaries** — `MessageSending`, `MessageSent`, `OtpGenerated`, `OtpVerified`

### Directory layout (collapsed)

```
src/
├── MsegatManager.php          # Fluent API entry point
├── MsegatClient.php           # HTTP transport layer
├── MsegatServiceProvider.php  # Service container binding
├── Facades/Msegat.php         # Facade
├── Enums/
│   ├── MessageStatus.php
│   ├── ApiEndpoint.php
│   └── ResponseCode.php
├── DTOs/
│   ├── SmsMessage.php
│   ├── OtpRequest.php
│   ├── DeliveryReport.php
│   └── BalanceResponse.php
├── Events/
│   ├── MessageSending.php
│   ├── MessageSent.php
│   ├── OtpGenerated.php
│   ├── OtpVerified.php
│   └── WebhookReceived.php
├── Listeners/
│   └── LogMessageListener.php
├── Jobs/
│   └── SendSmsJob.php
├── Notifications/
│   └── MsegatChannel.php
├── Http/
│   ├── Controllers/
│   │   └── WebhookController.php
│   └── Middleware/
│       └── VerifyWebhookSignature.php
├── Commands/
│   ├── CheckBalanceCommand.php
│   └── ListSendersCommand.php
├── Exceptions/
│   ├── MsegatException.php
│   ├── AuthenticationException.php
│   ├── InsufficientBalanceException.php
│   └── ValidationException.php
└── Support/
    └── PhoneNumberFormatter.php
config/msegat.php
database/migrations/
├── create_msegat_sms_logs_table.php
├── create_msegat_otp_requests_table.php
├── create_msegat_delivery_reports_table.php
├── create_msegat_webhook_logs_table.php
└── create_msegat_failed_requests_table.php
tests/
├── Unit/
├── Feature/
└── Integration/
```

---

## [SYSTEM_FLOW] — Verifiable User Journeys

### Journey 1: Send SMS
```
User → Msegat::sms()->to('9665xxxx')->message('Hi')->send()
  → MsegatManager creates SmsMessage DTO
  → dispatches MessageSending event
  → MsegatClient::post('sendsms.php', [...])
  → parses response code
  → dispatches MessageSent / MessageFailed
  → logs to msegat_sms_logs
  → returns typed SmsResponse
```
**Verify:** `$response->successful() === true` AND `msegat_sms_logs` has 1 row.

### Journey 2: Send & Verify OTP
```
User → Msegat::otp()->to('9665xxxx')->send()
  → generates code, calls sendOTPCode.php
  → stores hash in msegat_otp_requests
  → dispatches OtpGenerated

User → Msegat::otp()->to('9665xxxx')->verify('1234')
  → calls verifyOTPCode.php
  → dispatches OtpVerified / OtpFailed
  → updates msegat_otp_requests.status
```
**Verify:** OTP row transitions `pending → verified` on success.

### Journey 3: Webhook Delivery Report
```
Msegat → POST /webhook (signed payload)
  → VerifyWebhookSignature middleware (HMAC)
  → WebhookController::deliveryReport()
  → dispatches WebhookReceived
  → queues WebhookJob
  → updates msegat_delivery_reports
```
**Verify:** `msegat_webhook_logs` has entry with verified signature.

### Journey 4: Queue-based Sending
```
User → Msegat::sms()->to('9665xxxx')->message('Hi')->queue()
  → dispatches SendSmsJob to Laravel queue
  → worker processes job → calls send()
  → logs result
```
**Verify:** Job dispatched, processed, row in `msegat_sms_logs`.

---

## [LOGGING STRATEGY — Protocol 4]

- Uses Laravel's `Log::channel('msegat')` — async by default via `stack` channel
- Log levels: `error`, `warning`, `info` (no `debug` noise)
- Sensitive data (OTP codes, API keys) masked automatically
- Database logging via `msegat_sms_logs` table — dispatched asynchronously through events
- All HTTP requests/responses logged at `info` level: method, endpoint, status, duration

---

## [ORPHANS & PENDING]

| Item | Status | Note |
|------|--------|------|
| WhatsApp API endpoints | ⚠️ Undocumented | Public API not found; WhatsApp feature will use abstract driver pattern, default driver returns `NotImplementedException` until Msegat publishes REST docs |
| Webhook signature algorithm | ⚠️ Unknown | Will implement HMAC-SHA256 configurable; user provides secret in config |
| Bulk ID handling | ✅ Clarified | Response header format `M0000-{bulk_id}` |
| Sender name registration | ✅ Clarified | `addSender.php` endpoint exists |
| Contact lists API | ⚠️ Not found | Will omit for v1; can be added later |
| Scheduled messages | ✅ Supported | `timeToSend=later` + `exactTime` |

---

## [MILESTONES] — Verifiable Goals

### Milestone 1 — Foundation (config, client, auth)
- [x] composer.json, service provider, facade
- [x] config/msegat.php with all env vars
- [x] MsegatClient with retry, timeout, logging
- [ ] **Verify:** `php artisan vendor:publish --provider="Aghfatehi\Msegat\MsegatServiceProvider"` copies config

### Milestone 2 — SMS Core (send, bulk, personalized, cost, balance)
- [ ] SMS sending via fluent API
- [ ] Bulk SMS (array of numbers)
- [ ] Personalized SMS (variables per recipient)
- [ ] Balance inquiry
- [ ] Message cost calculation
- [ ] **Verify:** All Unit + Feature tests green

### Milestone 3 — OTP (send, verify, rate-limit)
- [ ] OTP send
- [ ] OTP verify
- [ ] OTP rate limiting
- [ ] **Verify:** `Msegat::otp()->to('9665xxxx')->send()` + `->verify('code')` flow

### Milestone 4 — WhatsApp (abstract driver + template)
- [ ] WhatsApp interface + `NullWhatsAppDriver`
- [ ] WhatsApp template variable substitution
- [ ] **Verify:** Code compiles; driver returns graceful error

### Milestone 5 — Database & Logging
- [ ] All migrations (sms_logs, otp_requests, delivery_reports, webhook_logs, failed_requests)
- [ ] Asynchronous audit logging
- [ ] **Verify:** `php artisan migrate` creates all 5 tables with indexes

### Milestone 6 — Webhooks
- [ ] WebhookController with signature verification
- [ ] Replay attack protection (timestamp nonce)
- [ ] Queue-based webhook processing
- [ ] **Verify:** POST `/webhook/msegat/delivery` with valid signature returns 200

### Milestone 7 — Laravel Ecosystem
- [ ] Notification channel (MsegatChannel)
- [ ] Artisan commands (balance, senders)
- [ ] Queue support (`->queue()` method on builder)
- [ ] Event system (all events dispatched and documented)
- [ ] **Verify:** `php artisan msegat:balance` returns balance

### Milestone 8 — Quality & CI
- [ ] PHPUnit 95%+ coverage
- [ ] PHPStan level 6
- [ ] Pint (PSR-12) pass
- [ ] GitHub Actions (PHP 8.2/8.3/8.4, Laravel 10/11/12 matrix)
- [ ] **Verify:** CI green on push

### Milestone 9 — Documentation & Release
- [ ] README.md (en + ar)
- [ ] Full PHPDoc on every class
- [ ] Packagist release v1.0.0
- [ ] **Verify:** `composer require aghfatehi/laravel-msegat` works

---

## [ARCHITECTURAL RULES]

1. **No code beyond the spec** — WhatsApp abstraction only; no undocumented features
2. **Single `MsegatClient`** — all HTTP goes through one class, no per-endpoint clients
3. **Fluent builders return self** — mutable builders, not immutable value objects (simpler DX)
4. **Events fire at boundaries** — before/after API calls, not inside HTTP layer
5. **DTOs are readonly** — once constructed, they represent a snapshot
6. **All exceptions extend `MsegatException`** — single catch possible
7. **Config validated on boot** — missing credentials throw early, not at send time
8. **Phone numbers normalized silently** — `05xxxxxxxx` → `9665xxxxxxxx` in `PhoneNumberFormatter`

---

> **Next step:** Awaiting approval before generating any code. The above architecture reduces the original 40+ file structure to ~25 files by collapsing unnecessary abstraction layers while maintaining all 14 core features, SOLID, and testability.
