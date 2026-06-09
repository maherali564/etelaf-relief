---
name: security-and-hardening
description: Hardens code against vulnerabilities. Use when handling user input, authentication, data storage, or external integrations.
---

# Security and Hardening

## Overview
Security-first development practices for web applications. Treat every external input as hostile, every secret as sacred, and every authorization check as mandatory.

## Process: Threat Model First
1. Map the trust boundaries — where does untrusted data cross into your system?
2. Name the assets — what's worth stealing or breaking?
3. Run STRIDE over each boundary
4. Write abuse cases next to use cases

## The Three-Tier Boundary System

### Always Do (No Exceptions)
- Validate all external input at the system boundary
- Parameterize all database queries
- Encode output to prevent XSS
- Use HTTPS for all external communication
- Hash passwords with bcrypt/argon2
- Set security headers (CSP, HSTS, X-Frame-Options)
- Use httpOnly, secure, sameSite cookies
- Run composer audit before every release

### Ask First
- Adding new authentication flows
- Storing new categories of sensitive data
- Adding new external service integrations
- Changing CORS configuration
- Adding file upload handlers

### Never Do
- Never commit secrets to version control
- Never log sensitive data
- Never trust client-side validation as a security boundary
- Never disable security headers
- Never use eval() with user-provided data
- Never expose stack traces to users

## OWASP Prevention Patterns

### Injection
```php
// Bad: SQL injection
$query = "SELECT * FROM users WHERE id = '$userId'";

// Good: Parameterized query
$user = DB::select('SELECT * FROM users WHERE id = ?', [$userId]);
```

### XSS Prevention
```blade
{{-- Bad --}}
{!! $userInput !!}

{{-- Good --}}
{{ $userInput }}
```

### Broken Access Control
```php
// Always check authorization, not just authentication
$this->authorize('view', $donation);
```

### Input Validation
```php
// Validate at the boundary, trust internal code
$request->validate([
    'email' => 'required|email|max:255',
    'amount' => 'required|numeric|min:1|max:10000',
]);
```

### File Upload Safety
```php
$request->validate([
    'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
]);
$filename = Str::random(40) . '.' . $request->file('photo')->extension();
```

## Rate Limiting
```php
Route::middleware(['throttle:30,1'])->group(function () {
    Route::post('/donate', [DonationController::class, 'store']);
});
Route::post('/webhook/stripe', [WebhookController::class, 'stripe'])->middleware('throttle:60,1');
```

## Secrets Management
- .env.example → Committed (template with placeholder values)
- .env → NOT committed (contains real secrets)
- If a secret is ever committed, rotate it immediately

## Red Flags
- User input passed directly to database queries or HTML rendering
- Secrets in source code or commit history
- API endpoints without auth checks
- Missing CORS configuration
- No rate limiting on auth endpoints
- Stack traces exposed to users
- Dependencies with known critical vulnerabilities

## Verification
- [ ] composer audit shows no critical vulnerabilities
- [ ] No secrets in source code
- [ ] All user input validated at system boundaries
- [ ] Auth checked on every protected endpoint
- [ ] Security headers present
- [ ] Error responses don't expose internal details
- [ ] Rate limiting on auth endpoints
