---
name: eloquent-optimization
description: قواعد تحسين استعلامات Eloquent - تجنب N+1، استخدام Indexes، Caching
mode: build
---

# مهارة: تحسين استعلامات Eloquent

## 1. منع N+1 Queries

### خطأ (N+1)
```php
$donations = Donation::all();
foreach ($donations as $d) {
    echo $d->user->name; // N استعلام إضافي
}
```

### صحيح (Eager Loading)
```php
$donations = Donation::with('user', 'campaign')->get(); // 3 استعلامات فقط
```

### تحميل العلاقات المتداخلة
```php
$donations = Donation::with(['user', 'campaign.translations'])->get();
```

### تحميل شرطي
```php
$posts = Post::with(['comments' => fn($q) => $q->where('approved', true)])->get();
```

## 2. اختيار أعمدة محددة

### خطأ
```php
$users = User::all(); // SELECT *
```

### صحيح
```php
$users = User::select('id', 'name', 'email')->get();
$donations = Donation::with(['user:id,name,email'])->get();
```

## 3. إضافة Indexes عبر Migrations

| العمود | السبب |
|--------|-------|
| user_id, campaign_id | Foreign Keys |
| status | Filtering شائع |
| created_at | ترتيب زمني |
| email | بحث سريع |
| payment_intent_id | Idempotency check |

```php
Schema::table('donations', function (Blueprint $table) {
    $table->index('user_id');
    $table->index('status');
    $table->index('created_at');
    $table->index(['status', 'created_at']);
});
```

## 4. استخدام Cache للإحصائيات

```php
// بدون Cache
$total = Donation::sum('amount');

// مع Cache
$total = Cache::remember('total_donations', 3600, function () {
    return Donation::sum('amount');
});

// مسح Cache عند التحديث
Cache::forget('total_donations');
```

## 5. معالجة المجموعات الكبيرة

```php
// Cursor (ذاكرة قليلة)
foreach (Donation::cursor() as $donation) { }

// Chunk (للعمليات المجمعة)
Donation::chunk(500, function ($donations) { });
```

## 6. استخدام Subqueries
```php
$users = User::addSelect(['last_donation_date' => Donation::select('created_at')
    ->whereColumn('user_id', 'users.id')
    ->latest()->take(1)
])->get();
```

## 7. تجنب Lazy Loading في الإنتاج
```php
// في AppServiceProvider.php
Model::preventLazyLoading(!$this->app->isProduction());
```

## ملخص Optimization
1. منع N+1 باستخدام with()
2. إضافة Indexes للأعمدة المستخدمة في WHERE/JOIN/ORDER
3. استخدام select() لاختيار الأعمدة فقط
4. Cache للاستعلامات المتكررة
5. Cursor/Chunk للمجموعات الكبيرة
