---
description: مراجعة أمنية للملف المحدد
---
# Command: review — ساهم (Sahem)

## Description
قم بتحليل الكود في الملف المحدد. ركز على نقاط الضعف الشائعة في Laravel/Filament، وأخرج تقريراً مفصلاً بالأخطاء والتوصيات.

## Usage
```
@opencode review app/Models/Donation.php
@opencode review --files=app/Services/PaymentService.php
@opencode review --security   # Security-focused review only
@opencode review --full       # Full audit (default)
```

## What This Does
1. **Security Audit** — Checks:
   - Secrets in source code
   - SQL injection vectors
   - Missing authorization
   - Webhook security
   - Throttle middleware presence

2. **Quality Audit** — Checks:
   - PSR-12 compliance (via Pint)
   - N+1 queries
   - Slim controllers vs services
   - Form Request usage
   - Proper model definitions

3. **Output Format**
```
## Review Results
### Passed ✅
- N+1 query check: passed
- CSRF tokens: all present
- No secrets in code

### Failed ❌
- File: app/Http/Controllers/DonationController.php:45
  Issue: Logic in controller (should be in Service)
  Severity: MEDIUM
  Fix: Extract payment processing to DonationService::process()

### Warnings ⚠️
- File: resources/views/donate.blade.php:88
  Issue: Missing throttle middleware (check route)
  Severity: LOW
```
