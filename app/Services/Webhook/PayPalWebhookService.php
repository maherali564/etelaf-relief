<?php

/**
 * ──────────────────────────────────────────────────────────────
 * 🎯 الكلاس: PayPalWebhookService
 * ──────────────────────────────────────────────────────────────
 * 📌 الغرض:
 *    خدمة معالجة Webhooks الواردة من PayPal. تتعامل مع أحداث
 *    الموافقة على الطلب (CHECKOUT.ORDER.APPROVED) وإتمام البيع
 *    (PAYMENT.SALE.COMPLETED) للتبرعات المتكررة. تضمن الأمان
 *    عبر التحقق من التوقيع، idempotency، والتحقق من المبالغ.
 * 
 * 🔗 الاعتماديات:
 *    - BaseWebhookService ← الكلاس الأب الذي يوفّر الدوال المشتركة
 *    - PayPalService ← التحقق من توقيع PayPal وتنفيذ عمليات الـ capture
 *    - IdempotencyHelper ← منع معالجة الطلبات المكررة
 *    - Donation ← البحث عن التبرعات وإنشاء تبرعات متكررة
 *    - Log ← تسجيل الأحداث والأخطاء
 *    - Str ← إنشاء معرفات عشوائية للمعاملات
 * 
 * 📦 المسؤوليات:
 *    1. handle ← استقبال webhook والتحقق من التوقيع وتوجيه الحدث
 *    2. handleOrderApproved ← تنفيذ عملية capture للمبلغ بعد موافقة المتبرع
 *    3. handleSaleCompleted ← إنشاء تبرع متكرر عند إتمام عملية بيع
 * ──────────────────────────────────────────────────────────────
 */

namespace App\Services\Webhook;

use App\Exceptions\WebhookException;
use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Services\Payment\IdempotencyHelper;
use App\Services\Payment\PayPalService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayPalWebhookService extends BaseWebhookService
{
    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تهيئة الخدمة مع بوابة PayPal وتعيين المزوّد إلى "paypal".
     * 
     * 📥 المدخلات:
     *    - $gateway: PaymentGateway ← كائن البوابة النشط (إعدادات API)
     * ──────────────────────────────────────────────────────────
     */
    public function __construct(PaymentGateway $gateway)
    {
        parent::__construct($gateway, 'paypal');
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handle
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    نقطة الدخول الرئيسية لمعالجة Webhook من PayPal.
     *    تتحقق من توقيع الـ webhook عبر PayPalService، تسجّل الحدث،
     *    ثم توجّهه إلى المعالج المناسب حسب نوع الحدث.
     * 
     * 📥 المدخلات:
     *    - $payload: string ← جسم الـ webhook الخام (JSON)
     *    - $headers: array ← هيدرات HTTP للطلب
     * 
     * 📤 المخرجات:
     *    - array ← ['status' => 'ok'] عند النجاح
     * 
     * ❌ الاستثناءات:
     *    - WebhookException ← إذا فشل التحقق من توقيع PayPal
     * 
     * 🔗 الاعتماديات:
     *    - PayPalService::verifyWebhook ← التحقق من التوقيع
     *    - logWebhook ← تسجيل الحدث
     * ──────────────────────────────────────────────────────────
     */
    public function handle(string $payload, array $headers): array
    {
        $service = new PayPalService($this->gateway->config ?? []);
        $verified = $service->verifyWebhook($payload, $headers);

        if (! $verified) {
            throw new WebhookException('Invalid signature');
        }

        $data = json_decode($payload, true);
        $eventType = $data['event_type'] ?? '';

        $this->logWebhook($eventType, $payload);

        match ($eventType) {
            'CHECKOUT.ORDER.APPROVED' => $this->handleOrderApproved($data),
            'PAYMENT.SALE.COMPLETED' => $this->handleSaleCompleted($data),
            default => null,
        };

        return ['status' => 'ok'];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handleOrderApproved
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة حدث CHECKOUT.ORDER.APPROVED من PayPal.
     *    بعد موافقة المتبرع على الطلب، تبحث عن التبرع المطابق،
     *    تتحقق من المبلغ، ثم تنفذ عملية capture لتحصيل المبلغ
     *    فعلياً من حساب المتبرع.
     * 
     * 📥 المدخلات:
     *    - $data: array ← بيانات الحدث كاملة من PayPal
     * 
     * 🔗 الاعتماديات:
     *    - findDonationByTransactionId ← البحث عن التبرع
     *    - isDonationPending ← التحقق من الحالة
     *    - verifyAmount ← مقارنة المبلغ
     *    - IdempotencyHelper::checkAndMark ← منع تكرار الـ capture
     *    - PayPalService::captureOrder ← تنفيذ تحصيل المبلغ
     * ──────────────────────────────────────────────────────────
     */
    private function handleOrderApproved(array $data): void
    {
        $orderId = $data['resource']['id'] ?? '';

        if (empty($orderId)) {
            return;
        }

        $donation = $this->findDonationByTransactionId($orderId);
        if (! $donation) {
            return;
        }

        if (! $this->isDonationPending($donation)) {
            return;
        }

        $purchaseUnits = $data['resource']['purchase_units'] ?? [];
        $webhookAmount = (float) ($purchaseUnits[0]['amount']['value'] ?? 0);
        if (! $this->verifyAmount($webhookAmount, (float) $donation->amount)) {
            return;
        }

        if (filled($donation->idempotency_key) && IdempotencyHelper::checkAndMark($donation->idempotency_key.'_capture')) {
            return;
        }

        try {
            $service = new PayPalService($this->gateway->config ?? []);
            $capture = $service->captureOrder($orderId);

            if (($capture['status'] ?? '') === 'COMPLETED') {
                $donation->update(['status' => 'completed']);
                Log::info('Donation completed via PayPal', ['donation_id' => $donation->id]);
            } else {
                Log::warning('PayPal capture returned non-completed status', [
                    'order_id' => $orderId,
                    'status' => $capture['status'] ?? 'unknown',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PayPal capture failed', [
                'order_id' => $orderId,
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handleSaleCompleted
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة حدث PAYMENT.SALE.COMPLETED من PayPal لإنشاء تبرع
     *    متكرر جديد. تنسخ بيانات المتبرع من التبرع الأصلي المرتبط
     *    بـ billing_agreement_id وتنشئ تبرعاً جديداً بحالة completed.
     * 
     * 📥 المدخلات:
     *    - $data: array ← بيانات حدث البيع من PayPal
     * 
     * 🔗 الاعتماديات:
     *    - IdempotencyHelper::checkAndMark ← منع تكرار معاملة البيع
     *    - Donation ← البحث عن التبرع الأصلي وإنشاء تبرع جديد
     *    - Str::random ← إنشاء transaction_id احتياطي
     * ──────────────────────────────────────────────────────────
     */
    private function handleSaleCompleted(array $data): void
    {
        $saleId = $data['resource']['id'] ?? '';
        $billingToken = $data['resource']['billing_agreement_id'] ?? '';

        if (empty($billingToken) || empty($saleId)) {
            return;
        }

        if (IdempotencyHelper::checkAndMark('paypal_sale_'.$saleId)) {
            Log::info('PayPal webhook: duplicate sale ignored', ['sale_id' => $saleId]);

            return;
        }

        $parentDonation = Donation::where('billing_agreement_id', $billingToken)->first();

        if (! $parentDonation) {
            Log::warning('PayPal webhook: parent donation not found', ['billing_agreement_id' => $billingToken]);

            return;
        }

        $amount = $data['resource']['amount']['total'] ?? 0;

        $donation = new Donation();
        $donation->fill([
            'donor_name' => $parentDonation->donor_name,
            'email' => $parentDonation->email,
            'phone' => $parentDonation->phone,
            'amount' => $amount,
            'currency' => $data['resource']['amount']['currency'] ?? 'USD',
            'status' => 'completed',
            'is_recurring' => true,
            'recurring_interval' => $parentDonation->recurring_interval,
            'is_anonymous' => $parentDonation->is_anonymous,
            'locale' => $parentDonation->locale,
            'donated_at' => now(),
        ]);
        $donation->donor_id = $parentDonation->donor_id;
        $donation->payment_method_id = $parentDonation->payment_method_id;
        $donation->transaction_id = $data['resource']['id'] ?? ('pp_'.Str::random(16));
        $donation->billing_agreement_id = $billingToken;
        $donation->project_id = $parentDonation->project_id;
        $donation->story_id = $parentDonation->story_id;
        $donation->idempotency_key = 'paypal_sale_'.$saleId;
        $donation->save();

        Log::info('Recurring donation created via PayPal', ['billing_agreement_id' => $billingToken, 'sale_id' => $saleId]);
    }
}
