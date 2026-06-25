<?php

/**
 * ──────────────────────────────────────────────────────────────
 * 🎯 الكلاس: StripeWebhookService
 * ──────────────────────────────────────────────────────────────
 * 📌 الغرض:
 *    خدمة معالجة Webhooks الواردة من Stripe. تتعامل مع أحداث
 *    إتمام الدفع (checkout.session.completed)، الفواتير المتكررة
 *    (invoice.paid)، وإلغاء الاشتراكات (customer.subscription.deleted).
 *    تضمن الأمان عبر التحقق من التوقيع، منع التكرار (idempotency)،
 *    والتحقق من تطابق المبالغ.
 * 
 * 🔗 الاعتماديات:
 *    - BaseWebhookService ← الكلاس الأب الذي يوفّر الدوال المشتركة
 *    - StripeService ← التحقق من توقيع Stripe وفك تشفير الحدث
 *    - IdempotencyHelper ← منع معالجة الطلبات المكررة
 *    - Donation ← إنشاء وتحديث التبرعات
 *    - Log ← تسجيل الأحداث والأخطاء
 *    - Str ← إنشاء معرفات عشوائية للفواتير
 * 
 * 📦 المسؤوليات:
 *    1. handle ← استقبال webhook والتحقق من التوقيع وتوجيه الحدث
 *    2. handleCheckoutCompleted ← إتمام تبرع لمرة واحدة
 *    3. handleInvoicePaid ← إنشاء تبرع متكرر عند دفع فاتورة اشتراك
 *    4. handleSubscriptionDeleted ← إيقاف التبرع المتكرر عند إلغاء الاشتراك
 * ──────────────────────────────────────────────────────────────
 */

namespace App\Services\Webhook;

use App\Exceptions\PaymentException;
use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Services\Payment\IdempotencyHelper;
use App\Services\Payment\StripeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripeWebhookService extends BaseWebhookService
{
    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تهيئة الخدمة مع بوابة Stripe وتعيين المزوّد إلى "stripe".
     * 
     * 📥 المدخلات:
     *    - $gateway: PaymentGateway ← كائن البوابة النشط (إعدادات API)
     * ──────────────────────────────────────────────────────────
     */
    public function __construct(PaymentGateway $gateway)
    {
        parent::__construct($gateway, 'stripe');
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handle
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    نقطة الدخول الرئيسية لمعالجة Webhook من Stripe.
     *    تستخرج التوقيع، تتحقق من صحته، تسجّل الحدث، وتوجّهه
     *    إلى المعالج المناسب حسب نوع الحدث.
     * 
     * 📥 المدخلات:
     *    - $payload: string ← جسم الـ webhook الخام (JSON)
     *    - $headers: array ← هيدرات HTTP (يجب أن تحتوي على Stripe-Signature)
     * 
     * 📤 المخرجات:
     *    - array ← ['status' => 'ok'] عند النجاح
     * 
     * ❌ الاستثناءات:
     *    - WebhookException ← إذا كان توقيع Stripe مفقوداً أو غير صالح
     * 
     * 🔗 الاعتماديات:
     *    - StripeService::verifyWebhook ← التحقق من التوقيع وفك التشفير
     *    - logWebhook ← تسجيل الحدث مع تنظيف البيانات الحساسة
     * ──────────────────────────────────────────────────────────
     */
    public function handle(string $payload, array $headers): array
    {
        $signature = $this->extractHeader($headers, 'Stripe-Signature');

        if (empty($signature)) {
            Log::warning('Stripe webhook: missing signature header');
            throw new PaymentException('Missing signature');
        }

        $service = new StripeService($this->gateway->config ?? []);

        try {
            $event = $service->verifyWebhook($payload, $signature);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook: '.$e->getMessage());
            throw new PaymentException('Invalid signature');
        }

        $this->logWebhook($event['type'] ?? '', $payload);

        match ($event['type'] ?? '') {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            'invoice.paid' => $this->handleInvoicePaid($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            default => null,
        };

        return ['status' => 'ok'];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handleCheckoutCompleted
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة حدث checkout.session.completed من Stripe.
     *    تبحث عن التبرع المطابق، تتحقق من حالته والمبلغ،
     *    ثم تحديثه إلى completed. تدعم الاشتراكات عبر تخزين
     *    stripe_subscription_id.
     * 
     * 📥 المدخلات:
     *    - $event: array ← بيانات الحدث من Stripe
     * 
     * 🔗 الاعتماديات:
     *    - IdempotencyHelper::checkAndMark ← منع التكرار
     *    - findDonationByTransactionId ← البحث عن التبرع
     *    - isDonationPending ← التحقق من الحالة
     *    - verifyAmount ← مقارنة المبلغ
     *    - completeDonation ← إتمام التبرع
     * ──────────────────────────────────────────────────────────
     */
    private function handleCheckoutCompleted(array $event): void
    {
        $sessionId = $event['data']['object']['id'] ?? '';
        $eventId = $event['id'] ?? '';

        if (empty($sessionId)) {
            return;
        }

        if (! empty($eventId) && IdempotencyHelper::checkAndMark($eventId)) {
            return;
        }

        $donation = $this->findDonationByTransactionId($sessionId);
        if (! $donation) {
            return;
        }

        if (! $this->isDonationPending($donation)) {
            return;
        }

        $webhookAmount = ($event['data']['object']['amount_total'] ?? 0) / 100;
        if (! $this->verifyAmount($webhookAmount, (float) $donation->amount)) {
            return;
        }

        $extraData = [];
        $subscriptionId = $event['data']['object']['subscription'] ?? null;
        if ($subscriptionId) {
            $extraData['stripe_subscription_id'] = $subscriptionId;
        }

        $this->completeDonation($donation, $extraData);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handleInvoicePaid
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة حدث invoice.paid من Stripe لإنشاء تبرع متكرر جديد
     *    عند دفع فاتورة اشتراك. تنسخ بيانات المتبرع من التبرع الأصلي
     *    وتنشئ تبرعاً جديداً بحالة completed.
     * 
     * 📥 المدخلات:
     *    - $event: array ← بيانات حدث الفاتورة من Stripe
     * 
     * 🔗 الاعتماديات:
     *    - IdempotencyHelper::checkAndMark ← منع تكرار الفاتورة
     *    - Donation ← البحث عن الاشتراك الأصلي وإنشاء تبرع جديد
     *    - Str::random ← إنشاء transaction_id احتياطي
     * ──────────────────────────────────────────────────────────
     */
    private function handleInvoicePaid(array $event): void
    {
        $invoiceId = $event['data']['object']['id'] ?? '';
        $subscriptionId = $event['data']['object']['subscription'] ?? '';

        if (empty($subscriptionId) || empty($invoiceId)) {
            return;
        }

        if (IdempotencyHelper::checkAndMark('stripe_invoice_'.$invoiceId)) {
            Log::info('Stripe webhook: duplicate invoice ignored', ['invoice_id' => $invoiceId]);

            return;
        }

        $parentDonation = Donation::where('stripe_subscription_id', $subscriptionId)->first();

        if (! $parentDonation) {
            Log::warning('Stripe webhook: parent donation not found for subscription', ['subscription_id' => $subscriptionId]);

            return;
        }

        $amount = ($event['data']['object']['amount_paid'] ?? 0) / 100;

        $donation = new Donation();
        $donation->fill([
            'donor_name' => $parentDonation->donor_name,
            'email' => $parentDonation->email,
            'phone' => $parentDonation->phone,
            'amount' => $amount,
            'currency' => $parentDonation->currency,
            'status' => 'completed',
            'is_recurring' => true,
            'recurring_interval' => $parentDonation->recurring_interval,
            'is_anonymous' => $parentDonation->is_anonymous,
            'locale' => $parentDonation->locale,
            'donated_at' => now(),
        ]);
        $donation->donor_id = $parentDonation->donor_id;
        $donation->payment_method_id = $parentDonation->payment_method_id;
        $donation->transaction_id = $event['data']['object']['id'] ?? ('inv_'.Str::random(16));
        $donation->project_id = $parentDonation->project_id;
        $donation->story_id = $parentDonation->story_id;
        $donation->stripe_subscription_id = $subscriptionId;
        $donation->idempotency_key = 'stripe_invoice_'.$invoiceId;
        $donation->save();

        Log::info('Recurring donation created via Stripe', ['subscription_id' => $subscriptionId, 'invoice_id' => $invoiceId]);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handleSubscriptionDeleted
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة حدث customer.subscription.deleted من Stripe.
     *    توقف التبرع المتكرر بتعيين is_recurring إلى false
     *    وإزالة recurring_interval لجميع التبرعات المرتبطة
     *    باشتراك Stripe معين.
     * 
     * 📥 المدخلات:
     *    - $event: array ← بيانات حدث إلغاء الاشتراك من Stripe
     * ──────────────────────────────────────────────────────────
     */
    private function handleSubscriptionDeleted(array $event): void
    {
        $subscriptionId = $event['data']['object']['id'] ?? '';

        if (empty($subscriptionId)) {
            return;
        }

        Donation::where('stripe_subscription_id', $subscriptionId)
            ->where('is_recurring', true)
            ->update(['is_recurring' => false, 'recurring_interval' => null]);

        Log::info('Subscription cancelled via Stripe', ['subscription_id' => $subscriptionId]);
    }
}
