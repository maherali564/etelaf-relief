<?php

namespace App\Services\Payment;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * ──────────────────────────────────────────────────────────────
 * 🏦 خدمة: PayPalService
 * ──────────────────────────────────────────────────────────────
 * 🔗 البوابة: PayPal (https://www.paypal.com)
 * 
 * 🎯 الوظيفة:
 *    تغليف كامل لبوابة الدفع PayPal، مسؤولة عن:
 *    - الحصول على Access Token (OAuth 2.0)
 *    - إنشاء طلبات الدفع (Orders API v2)
 *    - تأكيد (Capture) الطلبات بعد موافقة المستخدم
 *    - التحقق من تواقيع Webhook عبر PayPal Verification API
 * 
 * 📡 واجهة API المستخدمة:
 *    - POST /v1/oauth2/token ← مصادقة العميل (Client Credentials)
 *    - POST /v2/checkout/orders ← إنشاء طلب دفع
 *    - POST /v2/checkout/orders/{id}/capture ← تأكيد الدفع
 *    - POST /v1/notifications/verify-webhook-signature ← التحقق من Webhook
 * 
 * ⚙️ الإعدادات المطلوبة (config/services.php):
 *    - 'client_id' ← معرف تطبيق PayPal
 *    - 'client_secret' ← المفتاح السري للتطبيق
 *    - 'webhook_id' ← معرف Webhook (للتحقق من التوقيع)
 *    - 'mode' ← 'sandbox' للاختبار أو 'live' للإنتاج
 * 
 * 📤 تنسيق المخرجات:
 *    - createOrder: string ← رابط موافقة الدفع (approval URL)
 *    - captureOrder: array ← بيانات التأكيد من PayPal
 *    - verifyWebhook: bool ← نجاح/فشل التحقق
 * 
 * ❌ استثناءات:
 *    - RuntimeException ← فقدان إعدادات، فشل إنشاء الطلب، عدم وجود رابط دفع
 * 
 * 🔐 الأمان:
 *    - Idempotency Key عبر PayPal-Request-Id header
 *    - التحقق من Webhook عبر API مخصص (ليس مجرد توقيع محلي)
 *    - مهلات زمنية (10s timeout, 5s connect timeout)
 *    - تسجيل كامل للأخطاء مع تفاصيل الطلب
 * ──────────────────────────────────────────────────────────────
 */
class PayPalService
{
    protected array $config;

    protected string $baseUrl;

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تهيئة الخدمة بإعدادات PayPal وتحديد البيئة (sandbox/live)
     * 
     * 📥 المدخلات:
     *    - $config: array ← مصفوفة الإعدادات
     *      Required keys:
     *        • 'client_id' (string) ← معرف تطبيق PayPal
     *        • 'client_secret' (string) ← المفتاح السري
     *      Optional keys:
     *        • 'mode' (string) ← 'sandbox' (افتراضي) أو 'live'
     *        • 'webhook_id' (string) ← معرف Webhook للتحقق
     * 
     * 📤 المخرجات:
     *    - void
     * 
     * 💡 ملاحظات:
     *    - يحدد baseUrl تلقائياً حسب mode:
     *      • Sandbox: https://api-m.sandbox.paypal.com
     *      • Live: https://api-m.paypal.com
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا كان client_id أو client_secret فارغين
     * ──────────────────────────────────────────────────────────────
     */
    public function __construct(array $config)
    {
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            throw new RuntimeException('PayPal client ID and secret must be configured');
        }
        $this->config = $config;
        $this->baseUrl = ($config['mode'] ?? 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getAccessToken
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    الحصول على Access Token من PayPal باستخدام OAuth 2.0
     *    Client Credentials Grant. الرمز مطلوب لجميع استدعاءات API
     *    الأخرى (إنشاء الطلب، التأكيد، التحقق من Webhook).
     * 
     * 📥 المدخلات:
     *    - لا يوجد. يستخدم $this->config['client_id'] و
     *      $this->config['client_secret'] الممررين في المُنشئ.
     * 
     * 📤 المخرجات:
     *    - string ← Access Token (JWT) للاستخدام في الهيدير Authorization
     * 
     * 🔗 Endpoint:
     *    - POST {baseUrl}/v1/oauth2/token
     *    - Content-Type: application/x-www-form-urlencoded
     *    - Auth: Basic (client_id:client_secret)
     * 
     * ⏱ مهلات زمنية:
     *    - Timeout: 10 ثوانٍ للاستجابة
     *    - Connect Timeout: 5 ثوانٍ للاتصال
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا فشل الطلب أو لم يُرجع access_token
     * 
     * 🔐 تسجيل الأخطاء:
     *    - يسجل حالة HTTP ورسالة الخطأ من PayPal إن وجدت
     * ──────────────────────────────────────────────────────────────
     */
    protected function getAccessToken(): string
    {
        $response = Http::timeout(10)->connectTimeout(5)->withBasicAuth(
            $this->config['client_id'] ?? '',
            $this->config['client_secret'] ?? ''
        )->asForm()->post("{$this->baseUrl}/v1/oauth2/token", [
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->successful()) {
            Log::error('PayPal getAccessToken failed', [
                'status' => $response->status(),
                'error' => $response->json('error_description') ?? 'Unknown error',
            ]);
            throw new RuntimeException('Failed to get PayPal access token');
        }

        return $response->json('access_token');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: createOrder
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إنشاء طلب دفع (Order) في PayPal للتبرع المحدد. يُعيد رابط
     *    موافقة (approval URL) لتوجيه المستخدم إلى صفحة PayPal
     *    لإتمام الدفع.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد إنشاء الطلب له
     *      • $donation->amount: float ← المبلغ
     *      • $donation->currency: string ← العملة (USD, EUR, ...)
     *      • $donation->donor_name: string ← اسم المتبرع
     *      • $donation->locale: string ← اللغة
     *      • $donation->idempotency_key: string|null ← مفتاح منع التكرار
     * 
     * 📤 المخرجات:
     *    - string ← رابط PayPal لموافقة المستخدم (payer-action link)
     *      يتم توجيه المستخدم إليه لإتمام الدفع
     * 
     * 🧾 الآثار الجانبية:
     *    - تحديث $donation->transaction_id بـ Order ID من PayPal
     *    - تسجيل بدء الدفع (Log::info)
     * 
     * 🔗 عناوين URL المستخدمة:
     *    - return_url: route('payment.success', ...) ← بعد الدفع
     *    - cancel_url: route('payment.cancel', ...) ← عند الإلغاء
     *    ضمن experience_context في payment_source.paypal
     * 
     * 🔐 Idempotency:
     *    - يستخدم PayPal-Request-Id header لمنع تكرار الطلب
     *    - المفتاح من $donation->idempotency_key أو IdempotencyHelper
     * 
     * 📡 Endpoint:
     *    - POST {baseUrl}/v2/checkout/orders
     *    - Intent: CAPTURE (تأكيد فوري)
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا فشل إنشاء الطلب
     *    - RuntimeException ← إذا لم يُرجع PayPal Order ID
     *    - RuntimeException ← إذا لم يوجد رابط payer-action
     * 
     * ⚙️ تنسيق المبلغ:
     *    - يستخدم number_format($amount, 2) لضمان رقمين عشريين
     * ──────────────────────────────────────────────────────────────
     */
    public function createOrder(Donation $donation): string
    {
        Log::info('Payment initiated', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'gateway' => 'paypal',
        ]);

        $token = $this->getAccessToken();
        $idempotencyKey = $donation->idempotency_key ?? IdempotencyHelper::generateKey('paypal');

        $response = Http::timeout(10)->connectTimeout(5)->withToken($token)
            ->withHeader('PayPal-Request-Id', $idempotencyKey)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $donation->id,
                    'description' => 'تبرع - '.$donation->donor_name,
                    'amount' => [
                        'currency_code' => $donation->currency,
                        'value' => number_format($donation->amount, 2, '.', ''),
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'return_url' => route('payment.success', ['locale' => $donation->locale, 'donation' => $donation->id, 'token' => $donation->idempotency_key]),
                            'cancel_url' => route('payment.cancel', ['locale' => $donation->locale, 'donation' => $donation->id, 'token' => $donation->idempotency_key]),
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            Log::error('PayPal createOrder failed', [
                'donation_id' => $donation->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('فشل إنشاء طلب PayPal');
        }

        $data = $response->json();

        if (empty($data['id'])) {
            Log::error('PayPal createOrder: no order ID returned', ['donation_id' => $donation->id, 'response' => $data]);
            throw new RuntimeException('PayPal لم يُرجع رقم طلب');
        }

        $donation->update(['transaction_id' => $data['id']]);

        foreach ($data['links'] ?? [] as $link) {
            if (($link['rel'] ?? '') === 'payer-action') {
                return $link['href'];
            }
        }

        Log::error('PayPal createOrder: no payer-action link found', ['donation_id' => $donation->id, 'links' => $data['links'] ?? []]);
        throw new RuntimeException('لم يتم العثور على رابط الدفع PayPal');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: captureOrder
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تأكيد (Capture) طلب PayPal بعد موافقة المستخدم على الدفع.
     *    تُستدعى بعد إعادة توجيه المستخدم من PayPal إلى
     *    رابط النجاح (return_url).
     * 
     * 📥 المدخلات:
     *    - $orderId: string ← معرف الطلب (Order ID) من PayPal
     *      (مخزّن مسبقاً في $donation->transaction_id)
     * 
     * 📤 المخرجات:
     *    - array ← بيانات الاستجابة من PayPal
     *      تحتوي على:
     *      • id: string ← معرف التأكيد
     *      • status: string ← COMPLETED, PENDING, إلخ
     *      • purchase_units[].payments.captures[].id ← معرف الدفعة
     *      • purchase_units[].payments.captures[].amount ← المبلغ
     * 
     * 📡 Endpoint:
     *    - POST {baseUrl}/v2/checkout/orders/{orderId}/capture
     * 
     * ⏱ مهلات زمنية:
     *    - Timeout: 10 ثوانٍ
     *    - Connect Timeout: 5 ثوانٍ
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا فشل التأكيد (حالة HTTP غير ناجحة)
     * 
     * 💡 ملاحظة:
     *    - يتطلب Access Token صالحاً (يُحصل عليه في كل مرة)
     *    - يُسجّل تفاصيل الفشل مع Order ID وحالة HTTP
     * ──────────────────────────────────────────────────────────────
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();

        $response = Http::timeout(10)->connectTimeout(5)->withToken($token)->post(
            "{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture"
        );

        if (! $response->successful()) {
            Log::error('PayPal captureOrder failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('فشل تأكيد الدفع PayPal');
        }

        return $response->json();
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: verifyWebhook
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صحة طلب Webhook الوارد من PayPal باستخدام
     *    PayPal Verification API. يضمن أن الطلب حقيقي من PayPal
     *    ولم يتم التلاعب به.
     * 
     *    تختلف طريقة PayPal عن Stripe: PayPal يتطلب استدعاء API
     *    خاص للتحقق (وليس مجرد تشفير محلي).
     * 
     * 📥 المدخلات:
     *    - $payload: string ← نص الطلب الخام (raw request body) كـ JSON
     *    - $headers: array ← هيديرات الطلب كاملة
     *      المطلوبة للتحقق:
     *      • PAYPAL-AUTH-ALGO ← خوارزمية التوقيع
     *      • PAYPAL-CERT-URL ← رابط شهادة PayPal
     *      • PAYPAL-TRANSMISSION-ID ← معرف الإرسال
     *      • PAYPAL-TRANSMISSION-SIG ← التوقيع
     *      • PAYPAL-TRANSMISSION-TIME ← وقت الإرسال
     * 
     * 📤 المخرجات:
     *    - bool ← نجاح التحقق (true) أو فشله (false)
     * 
     * 📡 Endpoint:
     *    - POST {baseUrl}/v1/notifications/verify-webhook-signature
     *    - يُرسل الهيديرات الخمسة مع webhook_id والحدث
     * 
     * 🔐 عملية التحقق:
     *    1. الحصول على Access Token
     *    2. إرسال طلب POST إلى PayPal Verification API
     *    3. التحقق من أن verification_status === 'SUCCESS'
     *    4. إذا فشل، يُسجّل تحذير (Log::warning)
     * 
     * ❌ حالات الفشل (تُعيد false):
     *    - إذا كان webhook_id غير مهيأ (يسجّل خطأ حرج)
     *    - إذا فشل الحصول على Access Token
     *    - إذا فشل طلب التحقق أو كانت الحالة غير SUCCESS
     * ──────────────────────────────────────────────────────────────
     */
    public function verifyWebhook(string $payload, array $headers): bool
    {
        $webhookId = $this->config['webhook_id'] ?? '';

        if (empty($webhookId)) {
            Log::critical('PayPal webhook ID is not configured');

            return false;
        }

        try {
            $token = $this->getAccessToken();
        } catch (\Exception $e) {
            Log::error('PayPal verifyWebhook: failed to get access token', ['error' => $e->getMessage()]);

            return false;
        }

        $verificationSignature = ($headers['PAYPAL-AUTH-ALGO'] ?? '')
            .'|'.($headers['PAYPAL-CERT-URL'] ?? '')
            .'|'.($headers['PAYPAL-TRANSMISSION-ID'] ?? '')
            .'|'.($headers['PAYPAL-TRANSMISSION-SIG'] ?? '')
            .'|'.($headers['PAYPAL-TRANSMISSION-TIME'] ?? '');

        $response = Http::timeout(10)->connectTimeout(5)->withToken($token)->post(
            "{$this->baseUrl}/v1/notifications/verify-webhook-signature",
            [
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($payload, true),
            ]
        );

        $result = $response->successful() ? $response->json() : [];
        $verified = ($result['verification_status'] ?? '') === 'SUCCESS';

        if (! $verified) {
            Log::warning('PayPal webhook signature verification failed');
        }

        return $verified;
    }
}
