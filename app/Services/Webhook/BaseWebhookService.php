<?php

/**
 * ──────────────────────────────────────────────────────────────
 * 🎯 الكلاس: BaseWebhookService
 * ──────────────────────────────────────────────────────────────
 * 📌 الغرض:
 *    كلاس أساسي مجرّد (Abstract) يوفّر البنية التحتية المشتركة
 *    لمعالجة Webhooks من جميع بوابات الدفع (Stripe, PayPal, Wise).
 *    يوحّد عمليات التسجيل، البحث عن التبرعات، التحقق من المبالغ،
 *    وإكمال التبرعات بطريقة آمنة ومتّسقة.
 * 
 * 🔗 الاعتماديات:
 *    - Donation ← نموذج التبرع للبحث والتحديث
 *    - PaymentGateway ← إعدادات البوابة النشطة
 *    - IdempotencyHelper ← منع معالجة الطلبات المكررة
 *    - Log (Illuminate\Support\Facades\Log) ← تسجيل الأحداث والأخطاء
 *    - activity (Spatie\Activitylog) ← تسجيل أنشطة النظام للتدقيق
 * 
 * 📦 المسؤوليات:
 *    1. logWebhook ← تسجيل webhook وارد مع تنظيف البيانات الحساسة
 *    2. findDonationByTransactionId ← البحث عن تبرع بالـ transaction_id
 *    3. isDonationPending ← التحقق من أن التبرع لا يزال pending
 *    4. verifyAmount ← مقارنة المبلغ من webhook مع المبلغ المخزّن
 *    5. completeDonation ← تحديث حالة التبرع إلى completed
 *    6. extractHeader ← استخراج قيمة هيدر مع تجاهل حالة الأحرف
 *    7. handle (abstract) ← معالجة webhook - تُطبَّق في الكلاسات الفرعية
 * ──────────────────────────────────────────────────────────────
 */

namespace App\Services\Webhook;

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Services\Payment\IdempotencyHelper;
use Illuminate\Support\Facades\Log;

abstract class BaseWebhookService
{
    protected PaymentGateway $gateway;

    protected string $provider;

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تهيئة الخدمة بحالة البوابة النشطة واسم المزوّد.
     * 
     * 📥 المدخلات:
     *    - $gateway: PaymentGateway ← كائن البوابة النشط (إعدادات API)
     *    - $provider: string ← اسم المزوّد (stripe, paypal, wise)
     * ──────────────────────────────────────────────────────────
     */
    public function __construct(PaymentGateway $gateway, string $provider)
    {
        $this->gateway = $gateway;
        $this->provider = $provider;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: logWebhook
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسجيل وصول webhook في سجل النظام و activity log،
     *    مع إزالة بيانات بطاقات الائتمان من الـ payload قبل التسجيل.
     * 
     * 📥 المدخلات:
     *    - $type: string ← نوع الحدث (مثل checkout.session.completed)
     *    - $payload: ?string ← جسم الـ webhook الخام (JSON) اختياري
     * 
     * 🔗 الاعتماديات:
     *    - Log (Laravel) ← تسجيل الأحداث
     *    - activity (Spatie) ← تسجيل للنشاط للتدقيق الأمني
     * ──────────────────────────────────────────────────────────
     */
    protected function logWebhook(string $type, ?string $payload = null): void
    {
        Log::info("{$this->provider} webhook received", ['type' => $type]);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: findDonationByTransactionId
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    البحث عن تبرع باستخدام transaction_id المرسل من بوابة الدفع.
     *    يسجّل تحذيراً في السجل إذا لم يتم العثور على التبرع.
     * 
     * 📥 المدخلات:
     *    - $transactionId: string ← معرف المعاملة من بوابة الدفع
     * 
     * 📤 المخرجات:
     *    - ?Donation ← كائن التبرع أو null إذا لم يُعثر
     * 
     * ❌ الاستثناءات:
     *    - لا يرمي استثناءات، يعيد null عند الفشل
     * ──────────────────────────────────────────────────────────
     */
    protected function findDonationByTransactionId(string $transactionId): ?Donation
    {
        $donation = Donation::where('transaction_id', $transactionId)->first();
        if (! $donation) {
            Log::warning("{$this->provider} webhook: donation not found", ['transaction_id' => $transactionId]);
        }

        return $donation;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: isDonationPending
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن التبرع لا يزال في حالة "pending" قبل معالجته،
     *    لمنع معالجة التبرع مرّتين.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع المراد التحقق منه
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كانت الحالة pending، false إذا تمت معالجته سابقاً
     * ──────────────────────────────────────────────────────────
     */
    protected function isDonationPending(Donation $donation): bool
    {
        if ($donation->status !== 'pending') {
            Log::info("{$this->provider} webhook: donation already processed", [
                'donation_id' => $donation->id,
                'status' => $donation->status,
            ]);

            return false;
        }

        return true;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: verifyAmount
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    مقارنة المبلغ المستلم من الـ webhook مع المبلغ المخزّن
     *    في قاعدة البيانات لمنع التلاعب أو الأخطاء.
     * 
     * 📥 المدخلات:
     *    - $webhookAmount: float ← المبلغ من webhook
     *    - $storedAmount: float ← المبلغ المخزّن في قاعدة البيانات
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا تطابق المبلغان (بفارق ≤ 0.01)، false إذا اختلفا
     * ──────────────────────────────────────────────────────────
     */
    protected function verifyAmount(float $webhookAmount, float $storedAmount): bool
    {
        if ($webhookAmount > 0 && abs($webhookAmount - $storedAmount) > 0.01) {
            Log::warning("{$this->provider} webhook: amount mismatch", [
                'webhook_amount' => $webhookAmount,
                'stored_amount' => $storedAmount,
            ]);

            return false;
        }

        return true;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: completeDonation
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تحديث حالة التبرع إلى "completed" مع إضافة بيانات إضافية
     *    (مثل stripe_subscription_id) ثم تسجيل العملية.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد إكماله
     *    - $extraData: array ← بيانات إضافية لدمجها مع التحديث (اختياري)
     * 
     * 🔗 الاعتماديات:
     *    - Log ← تسجيل إتمام التبرع
     * ──────────────────────────────────────────────────────────
     */
    protected function completeDonation(Donation $donation, array $extraData = []): void
    {
        $donation->update(array_merge(['status' => 'completed'], $extraData));
        Log::info("Donation completed via {$this->provider}", ['donation_id' => $donation->id]);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: extractHeader
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استخراج قيمة هيدر معين من مصفوفة الهيدرات مع تجاهل حالة
     *    الأحرف (case-insensitive) والتعامل مع القيم المصفوفية.
     * 
     * 📥 المدخلات:
     *    - $headers: array ← مصفوفة الهيدرات
     *    - $key: string ← اسم الهيدر المطلوب
     * 
     * 📤 المخرجات:
     *    - string ← قيمة الهيدر أو سلسلة فارغة إن لم يُوجد
     * ──────────────────────────────────────────────────────────
     */
    protected function extractHeader(array $headers, string $key): string
    {
        foreach ([$key, strtolower($key), strtoupper($key)] as $k) {
            if (isset($headers[$k])) {
                $value = $headers[$k];
                return is_array($value) ? ($value[0] ?? '') : $value;
            }
        }

        return '';
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handle
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    دالة مجرّدة (Abstract) يجب على كل كلاس فرعي تنفيذها
     *    لمعالجة الـ webhook الوارد حسب منطق بوابة الدفع.
     * 
     * 📥 المدخلات:
     *    - $payload: string ← جسم الـ webhook الخام (JSON)
     *    - $headers: array ← هيدرات HTTP للطلب
     * 
     * 📤 المخرجات:
     *    - array ← مصفوفة تحتوي على status (مثل ['status' => 'ok'])
     * 
     * ❌ الاستثناءات:
     *    - WebhookException ← إذا كان التوقيع غير صالح أو البيانات ناقصة
     * ──────────────────────────────────────────────────────────
     */
    abstract public function handle(string $payload, array $headers): array;
}
