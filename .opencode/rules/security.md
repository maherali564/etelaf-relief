# Security Rules — ساهم (Sahem)
**Applies to:** All PHP, Blade, JavaScript, and configuration files in the project.  
**Priority:** CRITICAL (overrides any other rule if conflict).  
**Enforcement:** Agent must reject any code that violates these rules.

---

## Absolutely Forbidden (Never, Ever)

| Pattern | Reason | Alternative |
|---------|--------|-------------|
| `eval(`, `exec(`, `shell_exec(`, `system(`, `passthru(` | Arbitrary code execution → server compromise | Use native PHP functions or Laravel's `Process` facade with strict input validation |
| `unlink(`, `rm -rf` | File deletion without proper checks | Queue a cleanup job with explicit approval |
| `dd(`, `dump(`, `var_dump(`, `print_r(`, `ray(`, `Log::debug()` with secrets | Exposes internal data, may leak secrets | Use `Log::info()` with safe context, or remove before commit |
| `DB::raw(` without parameter binding | SQL injection | Use Eloquent or `DB::select('... WHERE id = ?', [$id])` |
| `{!! !!}` in Blade containing user input | XSS | Use `{{ }}` or run `e()` or `strip_tags()` |
| Hardcoded secrets: `sk_live_`, `sk_test_`, `paypal_`, `wise_`, `password =`, `api_key =` | Secret exposure in code | Use `env()` or `config()` |
| `@csrf` removed from any POST form | CSRF vulnerability | Keep `@csrf` in all forms that modify state |
| `$guarded = []` in any Eloquent model | Mass assignment vulnerability | Define `$fillable` instead |
| `COMPOSER_AUTH` or `.env` committed to git | Credential leak | Add `.env` to `.gitignore`, use `.env.example` |

---

## Authentication & Authorization (Laravel / Filament)

### Every Model Exposed via Controller or Filament MUST have a Policy
```php
php artisan make:policy DonationPolicy --model=Donation

// In Policy
public function view(User $user, Donation $donation): bool
{
    return $user->hasRole('super_admin') || $user->id === $donation->user_id;
}

// In controller method
$this->authorize('view', $donation);

// Filament Resource Authorization
public static function canViewAny(): bool
{
    return auth()->user()->can('view_any_donation');
}
```

### Role-Based Access (using spatie/laravel-permission)
- Roles: super_admin, admin, editor, user
- Never assign * to any role. Define exact permissions: view_donation, create_donation, edit_donation, delete_donation.

## Input Validation (All User Data)
### Always Use Form Requests
```php
php artisan make:request DonationRequest

// DonationRequest.php
public function rules(): array
{
    return [
        'email' => 'required|email|max:255',
        'amount' => 'required|numeric|min:1|max:10000',
        'payment_method' => 'required|in:stripe,paypal,wise',
    ];
}
```

### Throttle All Public POST Routes
```php
// In routes/web.php
Route::middleware(['throttle:30,1'])->group(function () {
    Route::post('/donate', [DonationController::class, 'store'])->name('donate.store');
});
```

## Payment Security (Stripe, PayPal, Wise)
### Webhook Signature Verification (Mandatory)
```php
// Stripe example
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig, config('services.stripe.webhook_secret'));
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    Log::error('Stripe webhook signature invalid', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'Invalid signature'], 400);
}
```

### Idempotency (Prevent Duplicate Processing)
```php
$existing = Donation::where('payment_intent_id', $event->payment_intent->id)->first();
if ($existing && $existing->status === 'completed') {
    return response()->json(['status' => 'already_processed']);
}
```

### Amount Verification
```php
$webhookAmount = $event->data->object->amount_received / 100;
if ((int)$webhookAmount !== (int)$donation->amount) {
    Log::warning('Amount mismatch', ['expected' => $donation->amount, 'received' => $webhookAmount]);
    return response()->json(['error' => 'Amount mismatch'], 400);
}
```

### Never Store Credit Card Information
- Do not store: card_number, cvv, expiry_month, expiry_year
- Store only: payment_intent_id, payment_method_id, last_four (for display)

## Database Security
### Mass Assignment Protection
```php
// In every model
protected $fillable = ['email', 'amount', 'status']; // explicitly allowed
protected $guarded = []; // NEVER do this
```

### Parameter Binding for Raw Queries
```php
// Correct
DB::select('SELECT * FROM users WHERE id = ?', [$id]);

// Forbidden
DB::select("SELECT * FROM users WHERE id = $id");
```

### Prevent N+1 (Performance + Security)
```php
$donations = Donation::with('user')->get(); // Good
```

## File Upload Security
```php
$request->validate([
    'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
]);

$filename = Str::random(40) . '.' . $request->file('photo')->extension();
$path = $request->file('photo')->storeAs('uploads', $filename, 'private');
```
- Never accept .php, .exe, .sh, .js (for uploads)
- Store files outside public/ or use .htaccess to block direct execution

## Security Testing Requirements
- Every security-sensitive change must include tests
- Use Pest to test edge cases:
```php
it('rejects invalid stripe webhook signature', function () {
    $response = $this->postJson('/webhook/stripe', [], ['Stripe-Signature' => 'invalid']);
    $response->assertStatus(400);
});
```

## Environment & Secrets Management
```text
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_WEBHOOK_ID=...

WISE_API_KEY=...
WISE_PROFILE_ID=...
```
- Never commit .env to git
- In code, access secrets via env() or config(), never hardcoded

## Agent Instructions
- When you see a violation, do not write the code. Explain why it's insecure and propose the correct alternative.
- If you are unsure whether a piece of code is secure, ask the user for clarification before proceeding.
- Always prefer Laravel's built-in security features (Eloquent, CSRF, validation, authorisation) over custom solutions.
- For payment code, always double-check: signature verification, idempotency, amount match, and logging.
- Never skip or comment out these rules unless the user explicitly approves and documents the exception.

## Quick Checklist Before Finalising Any Code
- No secrets in source code (check .env, config files, controllers, models)
- All user inputs validated via Form Requests
- All database queries use Eloquent or parameter binding
- All models have $fillable (not $guarded = [])
- All POST routes have @csrf in Blade forms
- All public POST routes have throttle middleware
- Webhook endpoints verify signature and idempotency
- File uploads are validated, renamed, and stored securely
- Policies exist and are used in controllers / Filament
- Tests cover at least one security scenario for the changed code

**Version:** 2.0.0 | **Last updated:** 2026-06-09 | **Maintainer:** Lead Security Engineer
