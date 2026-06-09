# Memory Bank — ساهم (Sahem) — النسخة النهائية

## Project Identity
- **Name:** ساهم (Sahem) — Donation & Humanitarian Relief Platform
- **Repository:** sahem/platform
- **License:** MIT
- **Target:** Gaza humanitarian relief
- **Environment Domains:** sahem.org (prod), staging.sahem.org (staging), local.sahem.test (dev)

## Technology Stack (Immutable)
| Layer | Technology | Version | Notes |
|-------|-----------|---------|-------|
| Backend | Laravel | ^11.0 | Must use latest 11.x |
| Language | PHP | ^8.2 | 8.3 recommended |
| Admin Panel | Filament | ^3.2 | Use v3, not v4 |
| Database | SQLite (dev) / MySQL 8.0 (prod) | — | MySQL strict mode enabled |
| Testing | PHPUnit + Pest | ^11.0 | Pest for all new tests |
| Linting | Laravel Pint | ^1.13 | With Laravel preset |

## Key Dependencies
- **spatie/laravel-permission** — Roles: super_admin, admin, editor, user
- **spatie/laravel-translatable** — Multi-language DB content (5 languages)
- **spatie/laravel-activitylog** — Audit trail for payments, permissions, auth
- **barryvdh/laravel-dompdf** — PDF certificates & tax invoices
- **chillerlan/php-qrcode** — QR code generation
- **flowframe/laravel-trend** — Analytics trends in admin
- **stripe/stripe-php** — Stripe payment processing (main gateway)
- **guzzlehttp/guzzle** — HTTP client (PayPal, Wise APIs)

## Supported Languages
| Code | Language | Direction | Locale for PHP |
|------|----------|-----------|----------------|
| ar | العربية | RTL | ar_SA |
| en | English | LTR | en_US |
| es | Español | LTR | es_ES |
| id | Bahasa Indonesia | LTR | id_ID |
| tr | Türkçe | LTR | tr_TR |

## Payment Gateways
- **Stripe** — Credit/debit cards (main) — supports USD, EUR, GBP
- **PayPal** — PayPal wallet — supports USD, EUR
- **Wise** — Bank transfers (international) — supports 20+ currencies

## Architectural Decisions (Active & Immutable)
1. **Service Layer Pattern** — Business logic in `app/Services/`, not Controllers. All Services are stateless.
2. **Strategy Pattern for Payments** — `PaymentService` delegates to `StripeGateway`, `PayPalGateway`, `WiseGateway`. Adding new gateway requires only new class and config.
3. **Locale-Based Routing** — All public routes under `/{locale}` prefix. Webhooks are outside locale prefix (raw POST endpoints).
4. **Translation Strategy** — `spatie/laravel-translatable` for translatable DB columns. `lang/{locale}/*.php` for UI strings. No fallback language unless specified.
5. **Static Assets** — Files in `public/`. No Vite, no Webpack. Use Laravel Mix if needed but currently not.
6. **Chat System** — AJAX polling (3s interval) + Livewire admin. WebSockets intentionally NOT used due to infrastructure constraints.
7. **Soft Deletes** — Applied to `Donation`, `User`, `Campaign` models. Other models use hard deletes unless specified.

## Performance Baselines (Measured with Laravel Debugbar or Clockwork)
| Metric | Target | When to alert |
|--------|--------|----------------|
| Homepage load | < 2s | > 3s |
| Donation page queries | < 200ms | > 500ms |
| Admin dashboard queries | < 500ms | > 1s |
| Webhook processing (Stripe/PayPal/Wise) | < 5s | > 10s |
| N+1 queries per page | < 20 | > 30 |
| Memory usage per request | < 64MB | > 128MB |

## Security Baselines (Non-Negotiable)
- All POST routes have throttle middleware (at least 30 requests per minute for guests, 100 for logged-in).
- Webhook endpoints verify signature **before any processing**.
- Every model that is exposed via Filament or public routes has a Policy.
- Every Filament Resource implements `canViewAny()`, `canCreate()`, `canEdit()`, `canDelete()`.
- Mass assignment protection: `$fillable` defined in every model, never `$guarded = []`.
- No secrets in code: use `.env` for all API keys, passwords, tokens.
- All user-provided files are validated with `mimes:...`, renamed, and stored outside public path (or behind .htaccess).

## Forbidden Patterns (Code will be automatically rejected by the agent)
- `eval(`, `exec(`, `shell_exec(`, `system(`, `passthru(`
- `unlink(` or `rm -rf` anywhere (except in artisan commands with explicit approval)
- `dd(`, `dump(`, `var_dump(`, `print_r(`, `ray(`, `Log::debug()` with sensitive data
- `DB::raw(` without parameter binding
- `{!! !!}` in Blade containing user-submitted content (XSS risk)
- Hardcoded secrets: any string matching `sk_live_`, `sk_test_`, `paypal_`, `wise_` patterns outside `.env`
- `@csrf` removed from any POST form
- `$guarded = []` in any Eloquent model

## Testing Requirements (Mandatory for PR merge)
- **Coverage minimums:** Services >= 80%, Controllers >= 60%, Payment logic >= 90%.
- **Edge cases that must be tested:**
  - Duplicate webhook processing (idempotency using `payment_intent_id`).
  - Amount mismatch between database and gateway response.
  - Expired payment intents (Stripe: `payment_intent.canceled`).
  - Network timeouts: Guzzle retry 3 times with exponential backoff.
- **Test naming convention (Pest):** `it_handles_stripe_webhook_with_invalid_signature` (English, snake_case).
- **Test command:** `php artisan test --parallel` for CI, `php artisan test --coverage` for local.

## Logging & Error Handling (Production)
- **Log levels:** Payment failure → `Log::error()` with `donation_id`, `gateway`, `error_message`. Auth failures → `Log::warning()` with `email`, `ip`. Info → `Log::info()` for non-sensitive events.
- **Sensitive data never logged:** passwords, API keys, tokens, credit card numbers, CVV, full PAN.
- **Exception handling:** Catch specific exceptions (e.g., `Stripe\Exception\CardException`), not generic `Exception`. For payment gateways, always catch and rethrow as `PaymentException`.
- **User feedback:** Never show exception details to users. Return generic message like "Payment failed, please try again" or "An error occurred".

## Monitoring & Alerts (Production Environment)
- **Health check endpoint:** `/health` must return JSON with status, database connection, Redis connection (if used), and last webhook timestamp. Response time < 2s.
- **Alerts (triggered via Sentry/UptimeRobot):**
  - Failed webhook signature > 5 per hour → critical alert.
  - Payment gateway timeout > 10 seconds → high alert.
  - N+1 queries > 30 per page → warning sent to dev team.
  - Disk usage > 85% on production server → critical alert.
- **Tools:** Laravel Telescope (only in dev/staging), Sentry (error tracking), UptimeRobot (availability every 5 min).

## Infrastructure (Production — for agent to know constraints)
- **Web Server:** Nginx 1.24+ (or Apache 2.4+). PHP files handled via PHP-FPM.
- **PHP-FPM:** Version 8.2 with opcache enabled. `memory_limit = 256M`, `max_execution_time = 60s`.
- **Queue Driver:** `database` (default). Redis optional for high-traffic future.
- **Cache Driver:** `redis` (if available) with fallback to `file`.
- **Session Driver:** `database` (never `file` or `cookie`).
- **Backup Strategy:** Daily database backup at 2am (mysqldump to offsite storage). File backups weekly.

## Special Rules for Gaza Humanitarian Context
- Donations must be processed with **lowest possible fees** (Stripe has reduced fees for charities — check config).
- Certificate PDFs must include Arabic + English on same page.
- All pages must load fast even on slow connections (optimize images, use caching).
- High priority: Block fraudulent donations (use Stripe Radar or custom rules).

---
**Version:** 1.0.0  
**Last updated:** 2026-06-09  
**Maintainer:** Lead architect
