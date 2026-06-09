# Laravel Conventions — ساهم (Sahem)

**Applies to:** All Laravel-specific files: Controllers, Requests, Services, Blade views, routes, config files, etc.  
**Priority:** HIGH (maintainability & consistency).  
**Enforcement:** Agent must follow these rules when writing or modifying Laravel code.

---

## 1. Routing

### Locale Prefix for Public Routes
```php
// routes/web.php
Route::prefix('{locale}')->where(['locale' => 'ar|en|es|id|tr'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/donate', [DonationController::class, 'create'])->name('donate.create');
    Route::post('/donate', [DonationController::class, 'store'])->name('donate.store')->middleware('throttle:donations');
});

// Webhooks (no locale, no CSRF)
Route::post('/webhook/stripe', [WebhookController::class, 'stripe'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

- Every route must have a unique name (dot notation: resource.action).
- In production, run `php artisan route:cache` after changes (never in development).

## 2. Controllers (Slim)
```php
namespace App\Http\Controllers;

use App\Http\Requests\DonationRequest;
use App\Services\DonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(protected DonationService $donationService) {}

    public function create(): View
    {
        return view('donate.create');
    }

    public function store(DonationRequest $request): RedirectResponse
    {
        $donation = $this->donationService->create($request->validated());
        return redirect()->route('donate.payment', $donation);
    }
}
```

**Forbidden in controllers:**
- Business logic (use Services)
- Direct DB::raw() or Eloquent queries without Service
- `$request->validate()` (use Form Requests)

## 3. Form Requests (Validation)
Generate: `php artisan make:request DonationRequest`
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or gate check
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|in:stripe,paypal,wise',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('validation.required', ['attribute' => 'email']),
        ];
    }
}
```

## 4. Services (Business Logic)
Location: `app/Services/`
```php
namespace App\Services;

use App\Models\Donation;
use App\Payment\StripeGateway;

class DonationService
{
    public function __construct(protected StripeGateway $stripeGateway) {}

    public function create(array $data): Donation
    {
        $donation = Donation::create($data);
        $this->stripeGateway->createPaymentIntent($donation);
        return $donation;
    }
}
```
- Services are stateless
- Inject dependencies via constructor
- Throw custom exceptions (e.g., `PaymentException`)
- Write unit tests for each public method

## 5. Blade Views
### Escaping (XSS Protection)
```blade
{{-- Good --}}
<h1>{{ $title }}</h1>

{{-- Forbidden with user input --}}
{!! $userHtml !!}
```

### Translation
```blade
<h1>{{ __('donate.title') }}</h1>
<p>{{ __('donate.description', ['amount' => $amount]) }}</p>
```

### RTL Support (Arabic)
```blade
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
```

**Forbidden in Blade:**
- Eloquent queries (use View Composers or pass data from controller)
- Complex PHP logic

## 6. Filament Admin
### Resource Authorization (Mandatory)
```php
public static function canViewAny(): bool
{
    return auth()->user()->can('view_any_donation');
}
```

### Role-Based Scoping in Tables
```php
Table::make()
    ->modifyQueryUsing(fn($query) => 
        auth()->user()->hasRole('super_admin') 
            ? $query 
            : $query->where('user_id', auth()->id())
    );
```

### Disable Global Search for Sensitive Models
```php
public static function getGloballySearchableAttributes(): array
{
    return [];
}
```

### Widget Caching
```php
protected function getData(): array
{
    return Cache::remember('donation_stats', 3600, fn() => [
        'total' => Donation::completed()->sum('amount'),
    ]);
}
```

## 7. Models
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Donation extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;
    
    protected $fillable = ['user_id', 'amount', 'currency', 'status'];
    protected $casts = ['amount' => 'decimal:2'];
    public array $translatable = ['title', 'description'];
    
    public function user(): BelongsTo { ... }
}
```
- Never use `$guarded = []`
- Define `$fillable` for every model

## 8. Translations
Language files in `lang/{locale}/`:
```
common.php, home.php, donate.php, admin.php, validation.php
```
Usage: `__('donate.title')`
When adding a new UI string, add translations for all 5 languages (ar, en, es, id, tr).

## 9. Caching
```php
$total = Cache::remember('total_donations', 3600, fn() => Donation::sum('amount'));
Cache::forget('total_donations');
```
- Prefer Redis in production
- Cache expensive queries (stats, aggregates)

## 10. Error Handling
```php
// app/Exceptions/PaymentException.php
class PaymentException extends Exception {}

try {
    $this->paymentService->charge($donation);
} catch (PaymentException $e) {
    Log::error('Payment failed', ['donation_id' => $donation->id, 'error' => $e->getMessage()]);
    return back()->with('error', __('donate.payment_failed'));
}
```
- Never expose exception details to users
- Log with context (donation_id, user_id, gateway)

## 11. Testing (Pest)
```php
uses(RefreshDatabase::class);

it('can create a donation', function () {
    $data = Donation::factory()->make()->toArray();
    $response = $this->post(route('donate.store', 'en'), $data);
    $response->assertRedirect();
    $this->assertDatabaseHas('donations', ['email' => $data['email']]);
});
```
- Write unit tests for Services, feature tests for endpoints
- Run `php artisan test` before every commit

## 12. Git & Commits
Conventional Commits:
```
feat(payment): add Stripe webhook idempotency
fix(donate): validate email uniqueness
security(auth): add rate limiting to login
test(donation): add edge case for duplicate webhook
```
- No merge without passing tests
- Pull requests require review

## Quick Checklist Before Committing
- Controller methods < 30 lines, delegate to Services
- Validation uses Form Request
- Routes named and grouped correctly
- No dd(), env(), DB::raw() without binding
- Blade uses {{ }} and __()
- Filament Resource has canViewAny() etc.
- New translations added for all 5 languages
- Tests pass (php artisan test)
- Code style passes (./vendor/bin/pint)

**Version:** 3.0.0 (Final) | **Last updated:** 2026-06-09 | **Maintainer:** Lead Laravel Architect
