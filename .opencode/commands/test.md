---
description: كتابة اختبارات Pest للملف المحدد
---
# Command: test — ساهم (Sahem)

## Description
اكتب اختبارات Pest للكود في الملف المحدد. استخدم الـ Factories والـ Fakes لبوابات الدفع. تأكد من اختبار الحالات الناجحة والفاشلة والحالات الحافة.

## Usage
```
@opencode test                        # Run all tests
@opencode test --filter=Donation      # Run specific tests
@opencode test --file=DonationService  # Test specific file
@opencode test --coverage             # Run with coverage report
@opencode test --write=DonationService # Write tests for DonationService
```

## What This Does
1. **Runs PHPUnit/Pest**
```powershell
php artisan test
```

2. **Runs Laravel Pint (Style Check)**
```powershell
./vendor/bin/pint --test
```

3. **If --write is specified**: Writes Pest test file for the given class:
```
tests/Feature/Services/DonationServiceTest.php
tests/Unit/Models/DonationTest.php
```

4. **Parses Results & Reports**
```
## Test Results
### PHPUnit (Pest)
- Tests: 47 passed, 0 failed, 3 skipped
- Time: 2.34s
- Coverage: 83.5%

### Pint (Code Style)
- Passed ✅ — PSR-12 compliant

### Issues Found
None. All checks passed.
```

## Test Writing Guidelines
- Test names: descriptive English, snake_case
- One `it()` per behavior
- Cover: success, failure, validation, authorization, edge cases
- Use `RefreshDatabase` trait for feature tests
- Mock external services (Stripe, PayPal) with `Mockery` or `Http::fake()`
