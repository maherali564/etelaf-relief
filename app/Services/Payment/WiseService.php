<?php

namespace App\Services\Payment;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * ──────────────────────────────────────────────────────────────
 * 🏦 خدمة: WiseService
 * ──────────────────────────────────────────────────────────────
 * 🔗 البوابة: Wise (https://wise.com) - سابقاً TransferWise
 * 
 * 🎯 الوظيفة:
 *    تغليف كامل لخدمة التحويل البنكي عبر Wise، مسؤولة عن:
 *    - توفير تعليمات الدفع (التحويل البنكي) للمتبرعين
 *    - توفير بيانات الحساب البنكي (IBAN, Swift, ...)
 *    - التحقق من تواقيع Webhook عبر HMAC-SHA256
 * 
 * 💡 ملاحظة مهمة:
 *    - Wise يختلف عن Stripe/PayPal: لا يوجد إنشاء جلسة دفع فورية.
 *    - يعتمد على التحويل البنكي اليدوي (Bank Transfer).
 *    - المتبرع يحول المبلغ يدوياً لحساب المنصة.
 *    - Webhook يُستخدم لتأكيد وصول الحوالة.
 * 
 * ⚙️ الإعدادات المطلوبة (config/services.php):
 *    - 'api_token' ← رمز API من Wise
 *    - 'webhook_secret' ← سر Webhook للتحقق من التوقيع
 *    - 'profile_id' ← معرف الملف الشخصي في Wise
 *    - 'mode' ← 'sandbox' للاختبار أو 'live' للإنتاج
 *    بيانات الحساب البنكي (اختياري):
 *    - 'bank_name', 'account_name', 'account_number'
 *    - 'iban', 'swift_code', 'routing_number', 'email'
 * 
 * 📤 تنسيق المخرجات:
 *    - getProfileId: string ← معرف الملف الشخصي
 *    - getPaymentInfo: array ← بيانات الحساب للتحويل
 *    - process: array ← تعليمات الدفع كاملة
 *    - verifyWebhook: ?array ← بيانات الحدث أو null عند الفشل
 * 
 * ❌ استثناءات:
 *    - RuntimeException ← إذا كان api_token غير مهيأ
 * 
 * 🔐 الأمان:
 *    - التحقق من Webhook عبر HMAC-SHA256 مع hash_equals
 *    - تسجيل كامل للأخطاء والتحذيرات
 * ──────────────────────────────────────────────────────────────
 */
class WiseService
{
    protected array $config;

    protected string $baseUrl;

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تهيئة الخدمة بإعدادات Wise وتحديد البيئة (sandbox/live)
     * 
     * 📥 المدخلات:
     *    - $config: array ← مصفوفة الإعدادات
     *      Required keys:
     *        • 'api_token' (string) ← رمز API من Wise
     *      Optional keys:
     *        • 'mode' (string) ← 'sandbox' (افتراضي) أو 'live'
     *        • 'webhook_secret' (string) ← سر Webhook
     *        • 'profile_id' (string) ← معرف الملف الشخصي
     *        • 'bank_name', 'account_name', 'account_number' ← بيانات البنك
     *        • 'iban', 'swift_code', 'routing_number', 'email'
     * 
     * 📤 المخرجات:
     *    - void
     * 
     * 💡 ملاحظات:
     *    - يحدد baseUrl تلقائياً حسب mode:
     *      • Sandbox: https://api.sandbox.transferwise.tech
     *      • Live: https://api.wise.com
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا كان api_token فارغاً
     * ──────────────────────────────────────────────────────────────
     */
    public function __construct(array $config)
    {
        if (empty($config['api_token'])) {
            throw new RuntimeException('Wise API token is not configured');
        }
        $this->config = $config;
        $this->baseUrl = ($config['mode'] ?? 'sandbox') === 'live'
            ? 'https://api.wise.com'
            : 'https://api.sandbox.transferwise.tech';
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getProfileId
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرجاع معرف الملف الشخصي (Profile ID) المسجل في Wise.
     *    يُستخدم هذا المعرف في عمليات Wise API اللاحقة.
     * 
     * 📥 المدخلات:
     *    - لا يوجد. يستخدم $this->config['profile_id'].
     * 
     * 📤 المخرجات:
     *    - string ← معرف الملف الشخصي (أو فارغ إذا لم يُهيأ)
     * 
     * 💡 ملاحظة:
     *    - إذا لم يكن profile_id معرفاً، تُعيد سلسلة فارغة.
     *    - يجب التحقق من القيمة قبل استخدامها في API.
     * ──────────────────────────────────────────────────────────────
     */
    public function getProfileId(): string
    {
        return $this->config['profile_id'] ?? '';
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getPaymentInfo
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرجاع بيانات الحساب البنكي التي سيتم عرضها للمتبرع
     *    لإتمام التحويل البنكي. تحتوي على جميع معلومات الحساب
     *    المطلوبة للتحويل.
     * 
     * 📥 المدخلات:
     *    - لا يوجد. يستخدم $this->config بالإعدادات التالية:
     *      • bank_name: string ← اسم البنك (افتراضي: 'Wise')
     *      • account_name: string ← اسم صاحب الحساب
     *      • account_number: string ← رقم الحساب
     *      • iban: string ← رقم IBAN الدولي
     *      • swift_code: string ← رمز Swift/BIC
     *      • routing_number: string ← رقم التوجيه (للبنوك الأمريكية)
     *      • email: string ← البريد الإلكتروني للحساب
     * 
     * 📤 المخرجات:
     *    - array ← بيانات الحساب البنكي (جميع الحقول المذكورة أعلاه)
     *      أي حقل غير مهيأ سيكون قيمته سلسلة فارغة
     * 
     * 🧾 مثال المخرجات:
     *    [
     *        'bank_name' => 'Wise',
     *        'account_name' => 'صندوق إغاثة غزة',
     *        'account_number' => '12345678',
     *        'iban' => 'GBXX...',
     *        'swift_code' => 'TRWIGBXX',
     *        'routing_number' => '',
     *        'email' => 'finance@sahem.org',
     *    ]
     * ──────────────────────────────────────────────────────────────
     */
    public function getPaymentInfo(): array
    {
        return [
            'bank_name' => $this->config['bank_name'] ?? 'Wise',
            'account_name' => $this->config['account_name'] ?? '',
            'account_number' => $this->config['account_number'] ?? '',
            'iban' => $this->config['iban'] ?? '',
            'swift_code' => $this->config['swift_code'] ?? '',
            'routing_number' => $this->config['routing_number'] ?? '',
            'email' => $this->config['email'] ?? '',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: process
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إنشاء تعليمات الدفع للتبرع عبر Wise أو التحويل البنكي.
     *    تُعيد معلومات التحويل للمتبرع (بدون إنشاء معاملة فورية).
     * 
     *    ملاحظة: على عكس Stripe/PayPal، هذه الدالة لا تنشئ
     *    جلسة دفع فورية. بدلاً من ذلك، تُعيد تعليمات للمتبرع
     *    ليحول المبلغ يدوياً.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد إنشاء التعليمات له
     *      • $donation->id: int ← معرف التبرع
     *      • $donation->amount: float ← المبلغ
     *      • $donation->currency: string ← العملة
     * 
     * 📤 المخرجات:
     *    - array ← تعليمات الدفع (لا تُحدث قاعدة البيانات):
     *      • type: string ← دائماً 'bank_transfer'
     *      • driver: string ← دائماً 'wise'
     *      • instructions: array ← بيانات الحساب البنكي
     *      • message: string ← رسالة للمتبرع باللغة العربية
     * 
     * 🧾 مثال المخرجات:
     *    [
     *        'type' => 'bank_transfer',
     *        'driver' => 'wise',
     *        'instructions' => ['bank_name' => 'Wise', ...],
     *        'message' => 'يرجى تحويل المبلغ عبر Wise أو التحويل البنكي أدناه',
     *    ]
     * 
     * 🧾 الآثار الجانبية:
     *    - تسجيل بدء عملية الدفع (Log::info)
     * 
     * ⚠️ ملاحظة:
     *    - لا تتحقق الدالة من إتمام التحويل.
     *    - يتم التأكيد عبر Webhook (verifyWebhook) عند وصول الحوالة.
     * ──────────────────────────────────────────────────────────────
     */
    public function process(Donation $donation): array
    {
        Log::info('Payment initiated', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'gateway' => 'wise',
        ]);

        return [
            'type' => 'bank_transfer',
            'driver' => 'wise',
            'instructions' => $this->getPaymentInfo(),
            'message' => 'يرجى تحويل المبلغ عبر Wise أو التحويل البنكي أدناه',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: verifyWebhook
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صحة طلب Webhook الوارد من Wise باستخدام
     *    HMAC-SHA256. يضمن أن الطلب حقيقي من Wise وأن
     *    محتواه لم يُعدَل.
     * 
     *    تختلف طريقة Wise عن Stripe/PayPal:
     *    - يستخدم HMAC-SHA256 مع مفتاح سري مشترك
     *    - يقارن التوقيع المحسوب مع X-Wise-Signature header
     *    - يستخدم hash_equals() لمقارنة آمنة (مقاومة timing attacks)
     * 
     * 📥 المدخلات:
     *    - $payload: string ← نص الطلب الخام (raw request body)
     *    - $signature: string ← قيمة هيدير X-Wise-Signature
     * 
     * 📤 المخرجات:
     *    - ?array ← بيانات الحدث (Array) إذا نجح التحقق
     *    - null ← إذا فشل التحقق (توقيع غير صالح أو إعدادات مفقودة)
     * 
     * 🔐 عملية التحقق:
     *    1. التحقق من وجود webhook_secret في الإعدادات
     *    2. التحقق من أن $signature غير فارغ
     *    3. حساب HMAC-SHA256 للـ payload باستخدام السر
     *    4. مقارنة التوقيع المحسوب مع الوارد (hash_equals)
     *    5. إذا تطابقا، فك ترميز JSON وإرجاع البيانات
     * 
     * ❌ حالات الفشل (تُعيد null):
     *    - إذا كان webhook_secret غير مهيأ (يسجّل خطأ حرج)
     *    - إذا كان التوقيع فارغاً (يسجّل تحذيراً)
     *    - إذا لم يتطابق التوقيع المحسوب مع الوارد (يسجّل تحذيراً)
     * 
     * 💡 ملاحظة أمنية:
     *    - استخدام hash_equals() إلزامي لمنع timing attacks
     *    - لا تستخدم مقارنة عادية (== أو ===) للتوقيع
     * ──────────────────────────────────────────────────────────────
     */
    public function verifyWebhook(string $payload, string $signature): ?array
    {
        $webhookSecret = $this->config['webhook_secret'] ?? '';

        if (empty($webhookSecret)) {
            Log::critical('Wise webhook secret is not configured');

            return null;
        }

        if (empty($signature)) {
            Log::warning('Wise webhook: empty signature header');

            return null;
        }

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! hash_equals($computedSignature, $signature)) {
            Log::warning('Wise webhook: signature mismatch');

            return null;
        }

        return json_decode($payload, true);
    }
}
