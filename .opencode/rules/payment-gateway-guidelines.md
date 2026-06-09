---
description: إرشادات صارمة للتعامل مع بوابات الدفع (Stripe, PayPal, Wise) – الأمان، idempotency، التحقق من التوقيع، ومعالجة الأخطاء.
mode: build
---

# Payment Gateway Guidelines — ساهم (Sahem)
**Applies to:**  
- `app/Services/PaymentService.php`  
- `app/Services/Payment/StripeGateway.php`  
- `app/Services/Payment/PayPalGateway.php`  
- `app/Services/Payment/WiseGateway.php`  
- `app/Http/Controllers/WebhookController.php`  
- Any file that processes payments or handles webhooks.

**Priority:** CRITICAL (financial integrity & security).  
**Enforcement:** Agent must follow these rules exactly; violations must be rejected.

---

## 1. Webhook Security (Non-Negotiable)

### Signature Verification (Do it FIRST)
```php
// Stripe
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig, config('services.stripe.webhook_secret'));
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    Log::error('Stripe webhook signature invalid', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'Invalid signature'], 400);
}

// PayPal
$headers = getallheaders();
if (!$gateway->verifyWebhookSignature($payload, $headers)) {
    Log::error('PayPal webhook signature failed');
    return response()->json(['error' => 'Invalid signature'], 400);
}

// Wise
// Use public key provided by Wise to verify JWT signature.
```

### Idempotency (Prevent Duplicates)
```php
$paymentIntentId = $event->data->object->payment_intent ?? $event->resource->id ?? null;
if (!$paymentIntentId) {
    Log::warning('Webhook missing payment_intent_id');
    return response()->json(['error' => 'Missing id'], 400);
}

$existing = Donation::where('payment_intent_id', $paymentIntentId)->first();
if ($existing && $existing->status === 'completed') {
    Log::info('Duplicate webhook ignored', ['payment_intent_id' => $paymentIntentId]);
    return response()->json(['status' => 'already_processed']);
}
```

### Amount Verification (Critical)
```php
$webhookAmount = $event->data->object->amount_received / 100; // cents → dollars
$donation = Donation::findOrFail($donationId);
if ((int) $webhookAmount !== (int) $donation->amount) {
    Log::warning('Amount mismatch', [
        'expected' => $donation->amount,
        'received' => $webhookAmount,
        'payment_intent_id' => $paymentIntentId,
    ]);
    return response()->json(['error' => 'Amount mismatch'], 400);
}
```

## 2. Storing Payment Information
### Allowed Fields in Database
- payment_intent_id (Stripe) / transaction_id (PayPal/Wise)
- payment_method_id (optional)
- last_four (last 4 digits of card, for display only)
- card_brand (Visa, Mastercard, etc.)

### Forbidden Fields (Never Store)
- Full card number (PAN)
- CVV / CVC
- Expiry month/year
- Any raw gateway response containing these fields

### Use Tokenization
```php
// Stripe: create PaymentIntent or SetupIntent
$intent = Stripe::paymentIntents()->create([
    'amount' => $donation->amount * 100,
    'currency' => $donation->currency,
    'payment_method_types' => ['card'],
]);

// Store only the intent ID
$donation->update(['payment_intent_id' => $intent->id]);
```

## 3. Payment Flow (Order of Operations)
1. User submits donation form → Controller validates via Form Request
2. DonationService::create() → creates Donation record with status pending
3. PaymentService::charge() → delegates to the appropriate gateway
4. Gateway returns a payment intent ID / redirect URL
5. User completes payment on gateway's hosted page
6. Webhook received → verify signature, idempotency, amount
7. DonationService::complete() → updates status to completed, clears cache, sends email

## 4. Testing Webhooks & Payments
```php
// In tests, swap the real gateway with a fake
$this->app->bind(PaymentGateway::class, FakePaymentGateway::class);

it('processes a valid stripe webhook', function () {
    $donation = Donation::factory()->create(['amount' => 5000, 'status' => 'pending']);
    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => ['id' => 'pi_test_123', 'amount_received' => 500000, 'currency' => 'usd']],
    ];
    $signature = $this->generateStripeSignature($payload);
    $response = $this->postJson('/webhook/stripe', $payload, ['Stripe-Signature' => $signature]);
    $response->assertOk();
    expect($donation->fresh()->status)->toBe('completed');
});
```

## 5. Logging (Mandatory)
```php
Log::info('Payment initiated', [
    'donation_id' => $donation->id,
    'amount' => $donation->amount,
    'currency' => $donation->currency,
    'gateway' => 'stripe',
]);

// On failure
Log::error('Payment failed', [
    'donation_id' => $donation->id,
    'error' => $e->getMessage(),
    'gateway' => 'stripe',
]);
```
**Never log:** credit card numbers, CVV, API keys, raw gateway responses that may contain secrets.

## 6. Retry & Timeout Handling
```php
// In Guzzle client
$client = new Client(['timeout' => 10, 'connect_timeout' => 5]);

// Retry Failed Gateway Calls (Exponential Backoff)
use Illuminate\Support\Retry;
$result = Retry::backoff(100, 3)->run(function () use ($gateway, $data) {
    return $gateway->charge($data);
}, function ($e) {
    return $e instanceof NetworkException;
});
```

## 7. Rate Limiting (Webhooks & Payment Endpoints)
```php
// routes/web.php
Route::post('/webhook/stripe', [WebhookController::class, 'stripe'])
    ->middleware('throttle:60,1');

Route::post('/donate', [DonationController::class, 'store'])
    ->middleware('throttle:30,1');
```

## Agent Instructions
- Before writing any payment code, verify that webhook signature verification and idempotency are implemented
- Never skip amount verification even if the gateway says it's safe
- Always store only the minimum required data (payment_intent_id, last_four)
- Use fakes in tests to avoid calling live gateways
- Log all relevant steps but exclude secrets
- If the user asks to bypass any of these rules (e.g., disable signature check), refuse and explain the risk

## Quick Checklist for Payment Code
- Webhook endpoint verifies signature before any logic
- Idempotency check using payment_intent_id
- Amount in webhook matches stored donation amount
- No credit card data stored in database
- Timeouts and retry logic configured
- Logging added (without secrets)
- Tests written for success, failure, duplicate webhook, and invalid signature
- Rate limiting applied on public payment and webhook endpoints

**Version:** 2.0.0 | **Last updated:** 2026-06-09 | **Maintainer:** Lead Payment Engineer
