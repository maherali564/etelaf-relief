# User Profile — ساهم (Sahem) — Personal Agent Configuration

## Identity
- **Role:** Lead Developer / Technical Architect
- **Project:** ساهم (Sahem) — Donation & Humanitarian Relief Platform
- **Experience Level:** Expert in Laravel, Filament, payment gateways, multi‑lingual systems
- **Communication Style:** Direct, clear, prefers bullet points and tables over long paragraphs

## Language & Communication

| Aspect | Preference |
|--------|------------|
| **Agent instructions language** | العربية (Arabic) — use Arabic for all guidance and explanations |
| **Code comments language** | English — write PHPDoc/TSDoc in English for public methods only (no internal noise) |
| **Variable/function naming** | English, `camelCase`, descriptive (e.g., `getUserDonations`, not `getData`) |
| **Commit messages language** | English — follow Conventional Commits (see below) |
| **Output detail level** | High — explain "why" and "how", not just "what" |

## Code Style & Quality Standards

| Category | Rule |
|----------|------|
| **Testing framework** | **Pest** — never use PHPUnit directly for new tests |
| **Linting & formatting** | Laravel Pint with `laravel` preset — run `./vendor/bin/pint` before commit |
| **Commit style** | Conventional Commits: `feat:`, `fix:`, `refactor:`, `test:`, `security:`, `docs:`, `chore:` |
| **Forbidden debugging artifacts** | Never commit: `dd()`, `dump()`, `var_dump()`, `print_r()`, `ray()`, `Log::debug()` with sensitive data |
| **No dead code** | Remove all commented‑out code blocks. If needed later, refer to git history. |
| **Maximum line length** | 120 characters (soft limit) |
| **Trailing whitespace** | Not allowed — trim automatically |

## Architecture Preferences (Active Decisions)

| Area | Preference | Why |
|------|------------|-----|
| **Business logic** | In `app/Services/`, never in Controllers | Testability, reusability, separation of concerns |
| **Caching** | `Cache::remember()` for queries that take >100ms or run often | Performance, reduce DB load |
| **Database queries** | Eager loading (`with()`) is **mandatory**; N+1 is forbidden | Performance baseline <200ms |
| **Validation** | **Form Request** classes always — never `$request->validate()` in controllers | Reusability, separation, cleaner controllers |
| **Admin panel** | Filament Resources with full auth checks (`canViewAny`, etc.) | Security, consistency |
| **Translations** | Add **all 5 languages** (ar, en, es, id, tr) for every new UI string | Consistency across the platform |
| **File storage** | Use `storage/app/` with access control; never serve user files directly from `public/` | Security |

## Workflow (Agent Behaviour)

1. **Plan first** — Before writing any code, explain the plan in Arabic. Include: affected files, new models, services, routes, security implications.
2. **Wait for approval** — Do not proceed until the user explicitly says "proceed", "ok", "go ahead", or similar.
3. **Implement** — Write code following all rules in `AGENTS.md`, `MEMORY.md`, and this `USER.md`.
4. **Test** — Run relevant Pest tests and fix failures. If tests do not exist, write them.
5. **Self‑review** — After implementation, review your own code for: security, N+1 queries, naming, documentation, and edge cases.
6. **Present results** — Summarise changes, mention any trade‑offs, and list pending actions (if any).

## Strict Security Rules (Never Negotiable)

| Rule | Consequence if broken |
|------|----------------------|
| Never write API keys, passwords, or secrets in code; always use `.env` | Agent must reject the request |
| Never disable SSL verification (e.g., `verify_peer => false`) | Agent must reject the request |
| Never bypass `@csrf` in Blade forms | Agent must reject the request |
| Never use `DB::raw()` without parameter binding | Agent must reject and suggest alternative |
| Always verify webhook signatures (Stripe, PayPal, Wise) before processing | Agent must add verification code |
| Always restrict Filament Global Search for sensitive models (`Donation`, `User`, `PaymentMethod`) | Agent must scope queries |
| Never store credit card details (PAN, CVV, expiry) in the database | Agent must use tokenisation |

## Preferred Tools & Methods

| Task | Tool / Method |
|------|---------------|
| **Database queries** | Eloquent ORM (with `with()`, `select()`, `cursor()` for large sets) |
| **Validation** | Form Request classes (e.g., `DonationRequest`) |
| **Admin UI** | Filament Resources, Pages, Widgets |
| **Frontend** | Blade, Swiper.js, Chart.js, Font Awesome (no Vue/React) |
| **Payments** | `PaymentService` abstraction → `StripeGateway`, `PayPalGateway`, `WiseGateway` |
| **Caching** | `Cache::remember()` with Redis (fallback to file) |
| **File storage** | `Storage::disk('private')->put()` for sensitive files; `public` disk only for avatars |
| **Notifications** | Mail via `NotificationService` (using Laravel Mailables) |
| **Background jobs** | Laravel Queues (database driver) for: sending emails, PDF generation, webhook retries |
| **PDF generation** | `barryvdh/laravel-dompdf` |
| **QR codes** | `chillerlan/php-qrcode` |

## Testing Preferences (Pest)

| Aspect | Rule |
|--------|------|
| **Test file location** | `tests/Feature/` for feature tests, `tests/Unit/` for service tests |
| **Test naming** | `it_does_something_under_condition` (English, snake_case, descriptive) |
| **Coverage minimum** | Services: 80%, Controllers: 60%, Payment logic: 90% |
| **Must test edge cases** | Duplicate webhook (idempotency), amount mismatch, gateway timeout, expired intent |
| **Test database** | Use `RefreshDatabase` trait for feature tests |
| **Mocking external APIs** | Use `Http::fake()` for PayPal/Wise, `Stripe::fake()` for Stripe |
| **Run tests before commit** | Always — `php artisan test --parallel` |

## Git & Commits

| Aspect | Rule |
|--------|------|
| **Branch naming** | `feature/xxx`, `fix/xxx`, `security/xxx`, `docs/xxx` |
| **Commit message format** | `<type>(<scope>): <subject>` — e.g., `feat(payment): add Stripe webhook idempotency` |
| **Types allowed** | `feat`, `fix`, `refactor`, `test`, `security`, `docs`, `chore`, `perf` |
| **Subject line** | Imperative, present tense, no dot at the end, max 72 chars |
| **Pull requests** | Require at least one reviewer, all tests must pass, no merge conflicts |

## Agent Personality (How I Want You to Act)

- **Proactive** — If you see a potential security issue or performance bottleneck, raise it immediately.
- **Concise but thorough** — Explain decisions in 3‑5 sentences, not paragraphs. Use tables and lists when possible.
- **Respectful of the humanitarian context** — Donations are sensitive; double‑check payment logic.
- **Admit uncertainty** — If you are not sure, say "I am not 100% sure, but based on the docs..."
- **Never guess** — If you lack information, ask me instead of inventing.
- **Prefer Laravel native solutions** — Avoid adding new packages unless necessary.

## Examples of Good vs. Bad Behaviour

### Good
Plan for adding donation certificate PDF:
    Create app/PDF/DonationCertificate.php using barryvdh/laravel-dompdf.
    Add method generate(Donation $donation) that returns PDF binary.
    Store PDF in storage/app/private/certificates/ with unique name.
    Add link in user dashboard to download it (authorisation check).
    Write Pest test: it_generates_certificate_for_completed_donation.
Proceed?

### Bad
Here is the code for certificate:
... (writes 100 lines without plan)

## Version & Update Log

| Version | Date | Changes |
|---------|------|---------|
| 2.0.0 | 2026‑06‑09 | Complete rewrite: added testing preferences, tool table, git conventions, agent personality, examples. |
| 1.0.0 | 2026‑06‑08 | Initial version (basic preferences). |
