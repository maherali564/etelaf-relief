<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Models\PaymentGateway;
use App\Services\Webhook\PayPalWebhookService;
use App\Services\Webhook\StripeWebhookService;
use App\Services\Webhook\WiseWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 الكونترولر: WebhookController
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    استقبال ومعالجة Webhooks من بوابات الدفع الخارجية (Stripe、
 *    PayPal、Wise). يُحَوِّل كل طلب إلى الخدمة المختصة بعد
 *    التحقق من وجود البوابة النشطة في قاعدة البيانات.
 * 
 * 📋 المسارات التي يعالجها:
 *    POST /webhook/stripe  ← stripe()
 *    POST /webhook/paypal  ← paypal()
 *    POST /webhook/wise    ← wise()
 *    (بدون CSRF وبدون بادئة اللغة)
 * 
 * 🔗 الاعتماديات:
 *    - StripeWebhookService ← معالجة webhooks من Stripe
 *    - PayPalWebhookService ← معالجة webhooks من PayPal
 *    - WiseWebhookService   ← معالجة webhooks من Wise
 *    - PaymentGateway (Model) ← التحقق من وجود البوابة
 *    - WebhookException ← استثناءات فشل التحقق من التوقيع
 * 
 * ⚠️ ملاحظات:
 *    - جميع المسارات معفاة من CSRF protection
 *    - تسجل الأخطاء في Log مع تصنيف حسب نوع الخطأ
 *    - تعيد JSON response دائماً
 * ──────────────────────────────────────────────────────────────
 */
class WebhookController extends Controller
{
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: stripe
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استقبال Webhook من Stripe ومعالجته عبر StripeWebhookService.
     * 
     * 📥 المدخلات:
     *    - $request: Request ← طلب HTTP يحتوي على Payload + Header
     *      'Stripe-Signature' للتوقيع
     * 
     * 📤 المخرجات:
     *    - JsonResponse ← {status: "ok"} (200) أو {error: "..."} (4xx/5xx)
     * 
     * 🔗 الاعتماديات:
     *    - StripeWebhookService ← التحقق من التوقيع ومعالجة الحدث
     * 
     * ⚠️ ملاحظات:
     *    - يستخدم الدالة الخاصة process() للمعالجة الموحدة
     * ──────────────────────────────────────────────────────────────
     */
    public function stripe(Request $request): JsonResponse
    {
        return $this->process($request, 'stripe', StripeWebhookService::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: paypal
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استقبال Webhook من PayPal ومعالجته عبر PayPalWebhookService.
     * 
     * 📥 المدخلات:
     *    - $request: Request ← طلب HTTP يحتوي على Payload + Header
     *      للتوقيع
     * 
     * 📤 المخرجات:
     *    - JsonResponse ← {status: "ok"} (200) أو {error: "..."} (4xx/5xx)
     * 
     * 🔗 الاعتماديات:
     *    - PayPalWebhookService ← التحقق من التوقيع ومعالجة الحدث
     * ──────────────────────────────────────────────────────────────
     */
    public function paypal(Request $request): JsonResponse
    {
        return $this->process($request, 'paypal', PayPalWebhookService::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: wise
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استقبال Webhook من Wise ومعالجته عبر WiseWebhookService.
     * 
     * 📥 المدخلات:
     *    - $request: Request ← طلب HTTP يحتوي على Payload + JWT
     *      للتوقيع
     * 
     * 📤 المخرجات:
     *    - JsonResponse ← {status: "ok"} (200) أو {error: "..."} (4xx/5xx)
     * 
     * 🔗 الاعتماديات:
     *    - WiseWebhookService ← التحقق من التوقيع ومعالجة الحدث
     * ──────────────────────────────────────────────────────────────
     */
    public function wise(Request $request): JsonResponse
    {
        return $this->process($request, 'wise', WiseWebhookService::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: process
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة موحدة لجميع Webhooks — البحث عن البوابة النشطة في
     *    قاعدة البيانات، إنشاء خدمة المعالجة، وتنفيذها مع معالجة
     *    الأخطاء.
     * 
     * 📥 المدخلات:
     *    - $request: Request ← طلب HTTP الأصلي
     *    - $driver: string ← اسم المُحرّك (stripe, paypal, wise)
     *    - $serviceClass: string ← اسم كلاس الخدمة (FQCN)
     * 
     * 📤 المخرجات:
     *    - JsonResponse ← إما result من الخدمة، أو خطأ 404/400/500
     * 
     * 🔗 الاعتماديات:
     *    - PaymentGateway::where('driver', $driver) ← البحث عن البوابة
     *    - $serviceClass::handle() ← معالجة الـ payload
     * 
     * ⚠️ ملاحظات:
     *    - دالة خاصة (private) — تستخدم داخلياً فقط
     *    - تعيد 404 إذا لم توجد بوابة نشطة
     *    - تعيد 400 إذا فشل التحقق من التوقيع (WebhookException)
     *    - تعيد 500 إذا حدث خطأ غير متوقع
     *    - تسجل جميع الأخطاء في Log مع تصنيفها
     * ──────────────────────────────────────────────────────────────
     */
    private function process(Request $request, string $driver, string $serviceClass): JsonResponse
    {
        $gateway = PaymentGateway::where('driver', $driver)->where('is_active', true)->first();
        if (! $gateway) {
            Log::warning("{$driver} webhook: active gateway not found");

            return response()->json(['error' => 'Gateway not found'], 404);
        }

        try {
            $service = new $serviceClass($gateway);
            $result = $service->handle($request->getContent(), $request->header());

            return response()->json($result);
        } catch (PaymentException $e) {
            Log::warning(ucfirst("{$driver} webhook validation failed"), ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error("{$driver} webhook processing failed", ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
