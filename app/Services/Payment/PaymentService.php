<?php

/**
 * ──────────────────────────────────────────────────────────────
 * 🎯 الخدمة: PaymentService
 * ──────────────────────────────────────────────────────────────
 * 📌 الغرض:
 *    نقطة الدخول الموحدة لجميع بوابات الدفع. تستخدم نمط
 *    Strategy لتوجيه عملية الدفع إلى البوابة المناسبة بناءً
 *    على driver البوابة (stripe, paypal, wise, bank_transfer,
 *    crypto, manual).
 * 
 * 🔗 الاعتماديات:
 *    - StripeService ← لمعالجة مدفوعات Stripe
 *    - PayPalService ← لمعالجة مدفوعات PayPal
 *    - WiseService ← لمعالجة مدفوعات Wise
 *    - BankTransferService ← للتحويلات البنكية
 *    - CryptoService ← للمدفوعات بالعملات الرقمية
 *    - ManualService ← للمدفوعات اليدوية
 * 
 * 📦 المسؤوليات:
 *    1. fromDonation() ← إنشاء الخدمة من التبرع
 *    2. initPayment() ← توجيه الدفع إلى البوابة الصحيحة
 * 
 * ⚠️ ملاحظات هيكلية:
 *    - 6 دوال init* متطابقة في النمط ← مخالفة DRY (تحتاج Strategy Pattern)
 *    - إضافة بوابة جديدة يتطلب تعديل هذه الفئة ← مخالفة OCP
 * ──────────────────────────────────────────────────────────────
 */

namespace App\Services\Payment;

use App\Models\Donation;
use App\Models\PaymentGateway;
use RuntimeException;

class PaymentService
{
    /**
     * كائن بوابة الدفع المُحمّل من قاعدة البيانات
     * يحتوي على config و driver و is_active
     */
    protected ?PaymentGateway $gateway;

    /**
     * إعدادات البوابة (مصفوفة من config JSON):
     * - Stripe: publishable_key, secret_key, webhook_secret
     * - PayPal: client_id, secret, webhook_id, mode
     * - Wise: api_key, profile_id
     * - Bank Transfer: account_details, bank_name
     * - Crypto: wallet_addresses per network
     * - Manual: instructions
     */
    protected array $config;

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض: إنشاء الخدمة مع تحميل إعدادات البوابة
     * 
     * 📥 المدخلات:
     *    - $gateway: PaymentGateway ← كائن البوابة من DB
     * 
     * ⚠️ ملاحظة:
     *    - config يُقرأ من حقل مشفر في DB (encrypted:array)
     * ──────────────────────────────────────────────────────────
     */
    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->config = $gateway->config ?? [];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: fromDonation
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إنشاء كائن PaymentService من التبرع المحدد عن طريق
     *    قراءة طريقة الدفع ← البوابة المرتبطة بها.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع الذي يحتوي على
     *      payment_method_id المرتبط بـ payment_gateway
     * 
     * 📤 المخرجات:
     *    - PaymentService ← كائن الخدمة الجاهز للاستخدام
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا لم توجد بوابة دفع مرتبطة
     * 
     * 📌 مثال:
     *    $payment = PaymentService::fromDonation($donation);
     *    $result = $payment->initPayment($donation);
     * ──────────────────────────────────────────────────────────
     */
    public static function fromDonation(Donation $donation): self
    {
        $gateway = $donation->paymentMethod?->gateway;

        if (! $gateway) {
            throw new RuntimeException('لا توجد بوابة دفع مرتبطة بطريقة الدفع هذه');
        }

        return new static($gateway);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: initPayment
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    توجيه عملية الدفع إلى البوابة المناسبة بناءً على
     *    driver البوابة. كل بوابة تعالج الدفع بطريقتها الخاصة
     *    وتُعيد نتيجة بنوعين:
     *      - 'redirect' ← تحويل المستخدم إلى بوابة الدفع
     *      - 'instructions' ← عرض تعليمات الدفع اليدوي
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد تحصيله
     * 
     * 📤 المخرجات:
     *    - array ← نتيجة الدفع:
     *      ['type' => 'redirect', 'url' => '...', 'message' => '...']
     *      ['type' => 'instructions', 'data' => [...], 'message' => '...']
     * 
     * ❌ الاستثناءات:
     *    - RuntimeException ← إذا كان driver غير معروف
     * 
     * 🔗 الاعتماديات:
     *    - StripeService::createCheckoutSession()
     *    - PayPalService::createOrder()
     *    - WiseService::process()
     *    - BankTransferService::process()
     *    - CryptoService::process()
     *    - ManualService::process()
     * ──────────────────────────────────────────────────────────
     */
    public function initPayment(Donation $donation): array
    {
        $driver = $this->gateway->driver;

        return match ($driver) {
            'stripe' => $this->initStripe($donation),
            'paypal' => $this->initPayPal($donation),
            'bank_transfer' => $this->initBankTransfer($donation),
            'wise' => $this->initWise($donation),
            'crypto' => $this->initCrypto($donation),
            'manual' => $this->initManual($donation),
            default => throw new RuntimeException("بوابة دفع غير مدعومة: $driver"),
        };
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: initStripe
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض: بدء دفع عبر Stripe → إنشاء Checkout Session
     *           → تحويل المستخدم إلى صفحة Stripe المدفوعة
     * 
     * 📤 المخرجات: ['type' => 'redirect', 'url' => Stripe URL]
     * ──────────────────────────────────────────────────────────
     */
    protected function initStripe(Donation $donation): array
    {
        $service = new StripeService($this->config);
        $url = $service->createCheckoutSession($donation);

        return [
            'type' => 'redirect',
            'url' => $url,
            'message' => 'جاري تحويلك إلى بوابة الدفع Stripe...',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: initPayPal
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض: بدء دفع عبر PayPal → إنشاء Order
     *           → تحويل المستخدم إلى PayPal للدفع
     * 
     * 📤 المخرجات: ['type' => 'redirect', 'url' => PayPal URL]
     * ❌ استثناء: إذا فشل الاتصال بـ PayPal
     * ──────────────────────────────────────────────────────────
     */
    protected function initPayPal(Donation $donation): array
    {
        $service = new PayPalService($this->config);
        $url = $service->createOrder($donation);

        if (! $url) {
            throw new RuntimeException('فشل الاتصال ببوابة PayPal');
        }

        return [
            'type' => 'redirect',
            'url' => $url,
            'message' => 'جاري تحويلك إلى بوابة الدفع PayPal...',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: initWise
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض: بدء دفع عبر Wise → عرض تعليمات التحويل
     * 
     * 📤 المخرجات: ['type' => 'instructions', 'data' => [...]]
     * ──────────────────────────────────────────────────────────
     */
    protected function initWise(Donation $donation): array
    {
        $service = new WiseService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'يرجى تحويل المبلغ عبر Wise',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: initBankTransfer
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض: بدء دفع عبر تحويل بنكي → عرض تفاصيل الحساب
     * 
     * 📤 المخرجات: ['type' => 'instructions', 'data' => [...]]
     * ──────────────────────────────────────────────────────────
     */
    protected function initBankTransfer(Donation $donation): array
    {
        $service = new BankTransferService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'يرجى تحويل المبلغ إلى الحساب البنكي',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: initCrypto
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض: بدء دفع بعملة رقمية → عرض عنوان المحفظة
     * 
     * 📤 المخرجات: ['type' => 'instructions', 'data' => [...]]
     * ──────────────────────────────────────────────────────────
     */
    protected function initCrypto(Donation $donation): array
    {
        $service = new CryptoService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'يرجى تحويل العملة الرقمية إلى عنوان المحفظة',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: initManual
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض: بدء دفع يدوي → تأكيد انتظار التواصل
     * 
     * 📤 المخرجات: ['type' => 'instructions', 'data' => [...]]
     * ──────────────────────────────────────────────────────────
     */
    protected function initManual(Donation $donation): array
    {
        $service = new ManualService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'سيتم التواصل معك لتأكيد التبرع',
        ];
    }
}
