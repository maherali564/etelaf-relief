<?php

namespace App\Services\Payment;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * ──────────────────────────────────────────────────────────────
 * 🏦 خدمة: StripeService
 * ──────────────────────────────────────────────────────────────
 * 🔗 البوابة: Stripe (https://stripe.com)
 * 
 * 🎯 الوظيفة:
 *    تغليف كامل لبوابة الدفع Stripe، مسؤولة عن:
 *    - إنشاء جلسات الدفع (Checkout Sessions) للتبرعات الفردية والمتكررة
 *    - التحقق من تواقيع Webhook (Stripe-Signature)
 *    - التعامل مع العمليات المتكررة (Subscriptions)
 * 
 * 📡 واجهة API المستخدمة:
 *    - Stripe Checkout Session API (Session::create)
 *    - Stripe Webhook Signature Verification (Webhook::constructEvent)
 * 
 * ⚙️ الإعدادات المطلوبة (config/services.php):
 *    - 'secret_key' (sk_...) ← مفتاح API السري
 *    - 'publishable_key' (pk_...) ← المفتاح العام
 *    - 'webhook_secret' (whsec_...) ← سر webhook للتحقق من التوقيع
 * 
 * 📤 تنسيق المخرجات:
 *    - createCheckoutSession: string ← رابط الدفع (Checkout Session URL)
 *    - verifyWebhook: array ← بيانات الحدث (Event) بعد التحقق
 * 
 * ❌ استثناءات:
 *    - RuntimeException ← في حال فقدان إعدادات أو فشل الاتصال
 * 
 * 🔐 الأمان:
 *    - Idempotency Key: يمنع تكرار الطلب
 *    - التحقق من توقيع Webhook إلزامي
 *    - تسجيل كامل لعمليات الدفع (بدون معلومات حساسة)
 * ──────────────────────────────────────────────────────────────
 */
class StripeService
{
    protected array $config;

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تهيئة الخدمة بمفاتيح Stripe والتحقق من وجود المفتاح السري
     * 
     * 📥 المدخلات:
     *    - $config: array ← مصفوفة الإعدادات
     *      Required keys:
     *        • 'secret_key' (string) ← مفتاح Stripe السري (sk_...)
     *      Optional keys:
     *        • 'publishable_key' (string) ← المفتاح العام (pk_...)
     *        • 'webhook_secret' (string) ← سر webhook (whsec_...)
     * 
     * 📤 المخرجات:
     *    - void
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا كان secret_key فارغاً أو غير موجود
     * ──────────────────────────────────────────────────────────────
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        if (empty($config['secret_key'])) {
            throw new RuntimeException('Stripe secret key is not configured');
        }
        Stripe::setApiKey($config['secret_key']);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: createCheckoutSession
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إنشاء جلسة دفع (Checkout Session) في Stripe للتبرع المحدد.
     *    تدعم التبرعات الفردية (payment mode) والتبرعات المتكررة
     *    (subscription mode) مع فترات شهرية/ربع سنوية/سنوية.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد إنشاء جلسة له
     *      • $donation->amount: float ← المبلغ (سيتم تحويله إلى سنتات)
     *      • $donation->currency: string ← العملة (مثل USD, EUR)
     *      • $donation->donor_name: string ← اسم المتبرع
     *      • $donation->locale: string ← اللغة (ar/en/es/id/tr)
     *      • $donation->is_recurring: bool ← هل هو تبرع متكرر؟
     *      • $donation->recurring_interval: string|null ← الفترة (monthly/quarterly/yearly)
     *      • $donation->idempotency_key: string|null ← مفتاح منع التكرار
     * 
     * 📤 المخرجات:
     *    - string ← رابط جلسة الدفع (Checkout Session URL)
     *      يتم إعادة توجيه المستخدم إليه لإتمام الدفع
     * 
     * 🧾 الآثار الجانبية:
     *    - تحديث $donation->transaction_id بـ Session ID
     *    - تحديث $donation->stripe_subscription_id (للمتكرر فقط)
     *    - تسجيل بدء عملية الدفع في السجلات (Log::info)
     * 
     * 🔗 عناوين URL المستخدمة:
     *    - success_url: route('payment.success', ...) ← بعد الدفع الناجح
     *    - cancel_url: route('payment.cancel', ...) ← عند إلغاء المستخدم
     *    كلا الرابطين يشملان locale و donation id و idempotency_key token
     * 
     * 🔐 Idempotency:
     *    - يستخدم IdempotencyHelper::generateKey('stripe') إن لم يوجد
     *    - يمرر المفتاح في خيارات Stripe: ['idempotency_key' => $key]
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا كان publishable_key فارغاً
     *    - RuntimeException ← إذا فشل إنشاء الجلسة (خطأ API أو شبكة)
     * ──────────────────────────────────────────────────────────────
     */
    public function createCheckoutSession(Donation $donation): string
    {
        if (empty($this->config['publishable_key'])) {
            throw new RuntimeException('Stripe publishable key is not configured');
        }

        Log::info('Payment initiated', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'gateway' => 'stripe',
        ]);

        $idempotencyKey = $donation->idempotency_key ?? IdempotencyHelper::generateKey('stripe');

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($donation->currency),
                    'product_data' => ['name' => 'تبرع - '.$donation->donor_name],
                    'unit_amount' => (int) ($donation->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('payment.success', ['locale' => $donation->locale, 'donation' => $donation->id, 'token' => $donation->idempotency_key]),
            'cancel_url' => route('payment.cancel', ['locale' => $donation->locale, 'donation' => $donation->id, 'token' => $donation->idempotency_key]),
            'metadata' => ['donation_id' => $donation->id],
        ];

        if ($donation->is_recurring && $donation->recurring_interval) {
            $intervalMap = [
                'monthly' => 'month',
                'quarterly' => 'month',
                'yearly' => 'year',
            ];
            $intervalCountMap = [
                'monthly' => 1,
                'quarterly' => 3,
                'yearly' => 1,
            ];
            $interval = $intervalMap[$donation->recurring_interval] ?? 'month';
            $intervalCount = $intervalCountMap[$donation->recurring_interval] ?? 1;

            $sessionParams['mode'] = 'subscription';
            $sessionParams['line_items'][0]['price_data']['recurring'] = [
                'interval' => $interval,
                'interval_count' => $intervalCount,
            ];
            unset($sessionParams['payment_method_types']);
            $sessionParams['subscription_data'] = [
                'metadata' => ['donation_id' => $donation->id],
            ];
        } else {
            $sessionParams['mode'] = 'payment';
        }

        try {
            $session = Session::create($sessionParams, ['idempotency_key' => $idempotencyKey]);

            $updateData = ['transaction_id' => $session->id];
            if ($donation->is_recurring && isset($session->subscription)) {
                $updateData['stripe_subscription_id'] = $session->subscription;
            }
            $donation->update($updateData);

            return $session->url;
        } catch (\Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('فشل إنشاء جلسة الدفع في Stripe: '.$e->getMessage());
        }
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: verifyWebhook
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صحة توقيع طلب Webhook الوارد من Stripe باستخدام
     *    مكتبة Stripe الرسمية. يضمن أن الطلب حقيقي من Stripe
     *    ولم يتم العبث به.
     * 
     * 📥 المدخلات:
     *    - $payload: string ← نص الطلب الخام (raw request body) كاملاً
     *    - $sigHeader: string ← قيمة هيدير Stripe-Signature
     *      (يحتوي على t=..., v1=..., وغيرها من المعاملات)
     * 
     * 📤 المخرجات:
     *    - array ← بيانات الحدث (Event) بعد التحقق بنجاح
     *      • type: string ← نوع الحدث (مثل payment_intent.succeeded)
     *      • data.object: array ← كائن الدفع/الاشتراك
     *      • id: string ← معرف الحدث (evt_...)
     * 
     * 🔐 عملية التحقق:
     *    1. يستخدم Stripe\Webhook::constructEvent()
     *    2. يقارن التوقيع المحسوب مع التوقيع في الهيدير
     *    3. يتحقق من صلاحية الطابع الزمني (tolerance)
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا كان webhook_secret غير مهيأ
     *    - RuntimeException ← إذا كان الـ payload غير صالح (UnexpectedValueException)
     *    - RuntimeException ← إذا فشل التحقق من التوقيع (SignatureVerificationException)
     * 
     * ⚠️ ملاحظات أمنية:
     *    - يُسجّل تحذيراً (Log::warning) عند فشل التحقق
     *    - يُسجّل خطأ حرج (Log::critical) عند عدم وجود secret
     *    - يجب أن يكون webhook_secret معرفاً في config/services.php
     * ──────────────────────────────────────────────────────────────
     */
    public function verifyWebhook(string $payload, string $sigHeader): array
    {
        $endpointSecret = $this->config['webhook_secret'] ?? '';
        if (empty($endpointSecret)) {
            Log::critical('Stripe webhook secret is not configured');
            throw new RuntimeException('Stripe webhook secret is not configured');
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            return $event->toArray();
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            throw new RuntimeException('Stripe webhook: invalid payload');
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: signature verification failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Stripe webhook: signature verification failed');
        }
    }
}
