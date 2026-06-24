<p dir="auto" align="center">
  <img src="https://sahemrelief.org/images/sahemlogo.svg" alt="Sahem for Relief and Development" width="120" />
  <h1 align="center">ساهم (Sahem) for Relief and Development</h1>
  <p align="center">Humanitarian donation & relief platform for Gaza — Multi-language, Multi-gateway</p>
</p>

<p dir="auto" align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?logo=laravel" />
  <img src="https://img.shields.io/badge/PHP-8.2+-purple?logo=php" />
  <img src="https://img.shields.io/badge/Filament-3.x-orange?logo=filament" />
  <img src="https://img.shields.io/badge/License-MIT-blue" />
</p>

---

## 📋 Overview

**ساهم (Sahem)** is a full-featured humanitarian donation and relief management platform supporting **Gaza relief operations**. It provides:

- **Public donation pages** with 6 payment gateways (Stripe, PayPal, Wise, Crypto, Bank Transfer, Manual)
- **Multi-language support**: العربية (ar), English (en), Español (es), Bahasa Indonesia (id), Türkçe (tr)
- **RTL layout** for Arabic
- **Filament admin panel** for managing donations, campaigns, projects, stories, volunteers, media, and more
- **Live chat** with Reverb WebSocket server
- **Real-time donor wall** with live polling
- **Automated recurring donations** via Stripe & PayPal subscriptions
- **PDF certificates** for donors
- **QR code generation** for campaigns
- **Blockchain transaction matching** for crypto donations

### Architecture

```
Next.js (Frontend)  ←→  Laravel API (Backend)  ←→  SQLite/MySQL
                           └── Filament Admin Panel
                           └── Reverb WebSocket Server
```

The **public website** (`https://sahemrelief.org`) is built with Next.js consuming Laravel as the backend. The **admin panel** runs on Laravel + Filament directly.

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 18+ (for build tools)
- SQLite (default) or MySQL 8+

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/your-org/sahem.git
cd sahem

# 2. Install PHP dependencies
composer install

# 3. Environment setup
copy .env.example .env
php artisan key:generate

# 4. Database (SQLite default)
php artisan migrate:fresh --seed

# 5. Storage link
php artisan storage:link

# 6. Frontend assets
npm install && npm run build

# 7. Start dev server
php artisan serve
```

### Default Admin Access

| Email | Password | Role |
|-------|----------|------|
| `admin@etelafrelief.org` | `password` | super_admin |

---

## 💳 Payment Gateways

| Gateway | Type | Webhooks | Recurring |
|---------|------|----------|-----------|
| Stripe | Credit Card | ✅ Signature verified | ✅ Subscriptions |
| PayPal | PayPal Wallet | ✅ Signature verified | ✅ Billing Agreements |
| Wise | Bank Transfer | ✅ JWT verified | ❌ |
| Crypto | Cryptocurrency | On-chain polling | ❌ |
| Bank Transfer | Manual Transfer | Admin review | ❌ |
| Manual | Offline | Admin review | ❌ |

### Webhook Security

All webhook endpoints verify signatures before processing:
- **Stripe**: `\Stripe\Webhook::constructEvent()` with `STRIPE_WEBHOOK_SECRET`
- **PayPal**: `$service->verifyWebhook()` with `PAYPHA_WEBHOOK_ID`
- **Wise**: JWT signature verification with public key
- **Idempotency**: Dedicated `idempotency_keys` table prevents duplicate processing

---

## 🗂 Database Structure (30+ Tables)

**Core Entities:**
- `users` — Admin/Filament users with roles (super_admin, admin, editor)
- `donors` — Registered donors (optional)
- `donations` — All donations (one-time + recurring children)
- `campaigns` — Fundraising campaigns (translatable)
- `projects` — Relief projects (translatable)
- `stories` — Beneficiary stories (translatable)

**Supporting Entities:**
- `payment_gateways` — Gateway configurations (config JSON encrypted)
- `payment_methods` — Active payment methods per gateway
- `cryptocurrencies` / `crypto_networks` — Crypto payment support
- `currency_rates` — Live exchange rates (cached)
- `posts` / `pages` — Content management (translatable)
- `sliders` — Homepage banners (translatable)
- `programs` — Relief programs (translatable)
- `statistics` / `gaza_stats` — Impact metrics
- `testimonials` — Beneficiary testimonials (translatable)
- `volunteers` / `volunteer_opportunities` — Volunteer management
- `newsletters` — Email subscribers
- `chat_sessions` / `chat_messages` — Live chat via Reverb
- `faqs` / `faq_categories` — FAQ system (translatable)
- `activity_log` — Spatie activity log
- `idempotency_keys` — Webhook deduplication
- `site_settings` — Dynamic settings (translatable)

---

## 🌍 Multi-Language System

Languages: `ar` (RTL), `en`, `es`, `id`, `tr`

- **Models**: `spatie/laravel-translatable` with `$translatable` arrays for JSON columns
- **Routes**: `/{locale}/` prefix with validation (`Route::pattern('locale', 'ar|en|es|id|tr')`)
- **Translations**: `lang/{locale}/*.php` files (common, home, donate, admin, validation, donor_wall, etc.)
- **RTL**: `<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">` in layout

---

## 🔒 Security

### Implemented Protections

| Category | Status |
|----------|--------|
| SQL Injection | ✅ Eloquent ORM everywhere, parameterized raw queries |
| XSS | ✅ Blade `{{ }}` escaping, `safeHtml()` with allowlist |
| CSRF | ✅ `@csrf` on all forms, webhook routes excluded |
| Mass Assignment | ✅ `$fillable` on all 30+ models |
| Rate Limiting | ✅ Throttle on all 21 public POST routes |
| Payment Encryption | ✅ `encrypted:array` cast on `payment_gateways.config` |
| Webhook Signatures | ✅ Stripe, PayPal, Wise all verified |
| Webhook Idempotency | ✅ Dedicated `idempotency_keys` table |
| Access Tokens | ✅ `hash_equals()` verification on payment pages |
| File Uploads | ✅ `mimes:` validation, renamed storage |
| Security Headers | ✅ HSTS, CSP, X-Frame-Options, X-Content-Type |
| CORS | ✅ Restricted to `APP_URL` |
| Session Security | ✅ HttpOnly, Secure, SameSite=Lax |
| Auth Throttling | ✅ Donor + Admin login rate limited |
| Honeypot | ✅ Spam protection on donation forms |

---

## ⚡ Performance

### Caching Strategy

| Cache Key | TTL | Invalidated By |
|-----------|-----|----------------|
| `home.*` | 3600s | `HomeCacheObserver` (on any model change) |
| `donation_page_data_*` | 300s | ❌ Not invalidated (should be) |
| `currency_rates` | 3600s | `CurrencyController` |
| `total_donations` | 3600s | `DonationObserver` |
| `admin_reports_*` | 300s | Time-based expiry |
| Filament widgets | 300s | Time-based expiry |

### Database Indexes

Indexes on foreign keys, `status`, `created_at`, `transaction_id`, `payment_intent_id`, `email`, `slug`, `is_active`. Missing indexes identified for `(is_recurring, status, recurring_interval)` — see action items.

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

Tests use **Pest** with `RefreshDatabase` trait.

---

## 🛠 Custom Artisan Commands

| Command | Description | Frequency |
|---------|-------------|-----------|
| `sahem:process-recurring` | Process recurring donation | Daily (cron) |
| `sahem:update-currency-rates` | Fetch live exchange rates | Hourly (cron) |
| `sahem:update-blockchain` | Poll blockchain for crypto donations | Every 15 min |
| `sahem:send-reminders` | Send donation reminders | Daily (cron) |

---

## 🐳 Deployment

### Production Checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Database driver: MySQL (not SQLite)
- [ ] Redis for cache + sessions
- [ ] `php artisan route:cache`, `config:cache`, `event:cache`
- [ ] Queue worker for async jobs
- [ ] Cron entry for `schedule:run`
- [ ] SSL certificate (HTTPS only)
- [ ] Reverb server for live chat
- [ ] `.env` with real payment keys

### Environment Variables

```env
APP_NAME=Sahem
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sahemrelief.org

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=sahem
DB_USERNAME=root
DB_PASSWORD=

# Payment Gateways
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_WEBHOOK_ID=...
WISE_API_KEY=...
WISE_PROFILE_ID=...

# Reverb (WebSocket)
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=0.0.0.0
REVERB_PORT=8080

# Other
CRYPTO_COMPARE_API_KEY=...
MAILCHIMP_API_KEY=...
```

---

## 📂 Project Structure

```
app/
├── Console/Commands/          # Artisan commands
├── Exceptions/                # Custom exceptions
├── Filament/
│   ├── Pages/                 # Custom Filament pages
│   ├── Resources/             # Filament CRUD resources
│   ├── Widgets/               # Dashboard widgets
│   └── Concerns/              # Shared Filament traits
├── Helpers/                   # Helper functions
├── Http/
│   ├── Controllers/           # Web controllers
│   ├── Livewire/              # Livewire components
│   ├── Middleware/             # HTTP middleware
│   └── Requests/              # Form request validation
├── Mail/                      # Mailables
├── Models/                    # Eloquent models
├── Observers/                 # Model observers
├── PDF/                       # PDF generation
├── Providers/                 # Service providers
├── Rules/                     # Custom validation rules
└── Services/                  # Business logic
    ├── Payment/               # Payment gateway integrations
    └── Webhook/               # Webhook handlers
bootstrap/
config/
database/
├── factories/
├── migrations/
└── seeders/
resources/
├── views/
│   ├── components/            # Blade components
│   ├── layouts/               # Layout templates
│   ├── partials/              # Reusable partials
│   ├── donate/                # Donation pages
│   ├── filament/              # Admin overrides
│   └── vendor/                # Package overrides
├── lang/                      # Translations (5 languages)
├── js/                        # JavaScript
└── css/                       # Stylesheets
routes/
├── web.php                    # Public routes
├── channels.php               # WebSocket channels
└── console.php                # Artisan routes
tests/
├── Feature/
└── Unit/
```

---

## 🧑‍💻 Development

### Code Style

```bash
./vendor/bin/pint    # Laravel Pint (PSR-12)
```

### Code Analysis

```bash
# Larascan (PHPStan for Laravel)
./vendor/bin/phpstan analyse --memory-limit=2G
```

### Contributing

1. Follow Conventional Commits: `feat:`, `fix:`, `security:`, `test:`, `refactor:`, `docs:`
2. Run `php artisan test` before commit
3. Must pass Pint formatting
4. Security-sensitive changes require test coverage

---

## 📄 License

MIT License — See [LICENSE](LICENSE) for details.

---

## 🤝 Support

- Email: `info@sahemrelief.org`
- Phone: `+32 472 84 34 16`
