# Database Rules — ساهم (Sahem)
**Applies to:** All migration files, Eloquent models, seeders, factories, and any raw database queries.  
**Priority:** HIGH (performance & data integrity).  
**Enforcement:** Agent must follow these rules when writing or modifying database code.

---

## 1. Migrations (Schema)

### Naming Conventions
| Object | Convention | Example |
|--------|------------|---------|
| Table names | plural, snake_case | `donations`, `user_profiles`, `campaign_translations` |
| Foreign key columns | `{singular}_id` | `user_id`, `campaign_id` |
| Pivot tables | singular alphabetical | `donation_campaign` (not `campaign_donation`) |
| Index names | `{table}_{column}_index` | `donations_user_id_index` |

### Required Columns (Every Table)
```php
$table->id();                      // primary key
$table->timestamps();              // created_at, updated_at
$table->softDeletes();             // for Donation, User, Campaign (others optional)
```

### Foreign Keys & Constraints
```php
// Correct: uses constrained() for referential integrity
$table->foreignId('user_id')->constrained()->cascadeOnDelete();

// If custom column name
$table->foreignId('created_by')->constrained('users')->nullOnDelete();
```

### Indexes (Performance)
```php
// Always add indexes for columns used in WHERE, JOIN, ORDER BY
$table->index('status');                     // simple
$table->index('created_at');                 // for sorting
$table->index(['status', 'created_at']);     // composite for common filters
$table->unique(['email', 'deleted_at']);     // unique with soft deletes
```

### Migration Example (Good)
```php
Schema::create('donations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('USD');
    $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
    $table->string('payment_intent_id')->nullable()->unique();
    $table->json('gateway_response')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('status');
    $table->index('created_at');
    $table->index(['status', 'created_at']);
    $table->index('payment_intent_id');
});
```

### Migrations are Immutable
- Never modify an existing migration after it has been committed to the repository.
- Always create a new migration for changes (add column, modify column, drop column).
- Use `php artisan make:migration add_xxx_to_donations_table`.

## 2. Eloquent Models
### Mandatory Properties
```php
class Donation extends Model
{
    use HasFactory, SoftDeletes, Translatable; // as needed
    
    // Always define $fillable (never $guarded = [])
    protected $fillable = [
        'user_id',
        'campaign_id',
        'amount',
        'currency',
        'status',
        'payment_intent_id',
    ];
    
    // Casts for type safety
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'deleted_at' => 'datetime',
    ];
    
    // For multilingual models (spatie/laravel-translatable)
    public array $translatable = ['title', 'description'];
}
```

### Relationships
```php
// Always define inverse side as well
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function campaign(): BelongsTo
{
    return $this->belongsTo(Campaign::class);
}
```

### Scopes for Reusable Queries
```php
public function scopeCompleted($query)
{
    return $query->where('status', 'completed');
}

public function scopeThisMonth($query)
{
    return $query->whereMonth('created_at', now()->month);
}
```

### Prevent N+1 (Eager Loading)
```php
// Always use with() when you know you'll need relations
$donations = Donation::with('user', 'campaign')->get();
```

## 3. Query Performance (Must Follow)
### Forbidden N+1
```php
// Bad
$donations = Donation::all();
foreach ($donations as $d) {
    echo $d->user->name; // extra query per donation
}

// Good
$donations = Donation::with('user')->get();
foreach ($donations as $d) {
    echo $d->user->name; // no extra queries
}
```

### Select Only Needed Columns
```php
// Bad
$users = User::all(); // SELECT *

// Good
$users = User::select('id', 'name', 'email')->get();
```

### Large Datasets: Use Cursor or Chunk
```php
// For processing large datasets (thousands of records)
foreach (Donation::cursor() as $donation) {
    // memory efficient
}

Donation::chunk(500, function ($donations) {
    // process in batches
});
```

### Caching for Expensive Queries
```php
$totalDonations = Cache::remember('total_donations', 3600, function () {
    return Donation::completed()->sum('amount');
});

// Clear cache when data changes
Cache::forget('total_donations');
```

## 4. Raw Queries (Only When Necessary)
If you must use DB::raw(), always use parameter binding:
```php
// Correct
DB::select('SELECT * FROM donations WHERE user_id = ? AND amount > ?', [$userId, $minAmount]);

// Named bindings
DB::select('SELECT * FROM donations WHERE user_id = :user_id', ['user_id' => $userId]);

// Forbidden (SQL injection risk)
DB::select("SELECT * FROM donations WHERE user_id = $userId");
```

## 5. Testing Database Code
Use RefreshDatabase trait:
```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_creates_donation_with_valid_data()
    {
        $donation = Donation::factory()->create(['amount' => 100]);
        $this->assertDatabaseHas('donations', ['id' => $donation->id]);
    }
}
```

## 6. Common Mistakes to Avoid
| Mistake | Why it's bad | Correct way |
|---------|-------------|-------------|
| $model->save() without validation | Invalid data may enter DB | Use Form Request + mass assignment |
| Forgetting $fillable | Mass assignment vulnerability | Define $fillable in every model |
| No indexes on foreign keys | Slow JOIN queries | Add $table->index('user_id') |
| Using DB::raw() unnecessarily | Harder to read, SQL injection risk | Use Eloquent or parameter binding |
| Not handling soft deletes | Queries include deleted records | Use withTrashed() when needed, otherwise default scope ignores them |
| Storing JSON as string | Cannot query inside JSON | Use $casts = ['data' => 'array'] |

## Quick Checklist Before Committing Database Changes
- New migration: table name plural, snake_case
- Migration includes id, timestamps(), soft deletes if needed
- Foreign keys use constrained()
- Indexes added for columns used in WHERE/JOIN/ORDER
- Model has $fillable (no $guarded = [])
- Relationships defined on both sides (if needed)
- Eager loading used (with()) in all queries that need relations
- No N+1 queries (verified with Debugbar or Clockwork)
- Tests cover at least one create/read/update operation
- Database cache cleared if adding new caching key

## Agent Instructions
- When writing migrations, always add indexes for foreign keys and commonly filtered columns.
- When creating a model, always define $fillable and $casts.
- When writing a query, prefer Eloquent over DB::raw(). If raw is necessary, use parameter binding.
- When adding a relation, use with() in any controller/service that will access it.
- When updating schema, always create a new migration, never modify an existing one.

**Version:** 2.0.0 | **Last updated:** 2026-06-09 | **Maintainer:** Lead Database Architect
