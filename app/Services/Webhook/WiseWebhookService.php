<?php

/**
 * ──────────────────────────────────────────────────────────────
 * 🎯 الكلاس: WiseWebhookService
 * ──────────────────────────────────────────────────────────────
 * 📌 الغرض:
 *    خدمة معالجة Webhooks الواردة من Wise (TransferWise سابقاً).
 *    تتعامل مع حدث تغيير حالة التحويل (transfer.state.change)
 *    لإتمام التبرعات عند نجاح التحويل البنكي.
 *    تضمن الأمان عبر التحقق من التوقيع، idempotency،
 *    والتحقق من تطابق المبلغ.
 * 
 * 🔗 الاعتماديات:
 *    - BaseWebhookService ← الكلاس الأب الذي يوفّر الدوال المشتركة
 *    - WiseService ← التحقق من توقيع Wise باستخدام المفتاح العام
 *    - IdempotencyHelper ← منع معالجة الطلبات المكررة
 *    - Donation ← البحث عن التبرعات وتحديثها
 *    - Log ← تسجيل الأحداث والأخطاء
 * 
 * 📦 المسؤوليات:
 *    1. handle ← استقبال webhook والتحقق من التوقيع وتوجيه الحدث
 *    2. handleTransferCompleted ← إتمام التبرع عند نجاح التحويل
 * ──────────────────────────────────────────────────────────────
 */

namespace App\Services\Webhook;

use App\Exceptions\PaymentException;
use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Services\Payment\WiseService;
use Illuminate\Support\Facades\Log;

class WiseWebhookService extends BaseWebhookService
{
    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تهيئة الخدمة مع بوابة Wise وتعيين المزوّد إلى "wise".
     * 
     * 📥 المدخلات:
     *    - $gateway: PaymentGateway ← كائن البوابة النشط (إعدادات API)
     * ──────────────────────────────────────────────────────────
     */
    public function __construct(PaymentGateway $gateway)
    {
        parent::__construct($gateway, 'wise');
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handle
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    نقطة الدخول الرئيسية لمعالجة Webhook من Wise.
     *    تستخرج التوقيع من الهيدر، تتحقق من صحته عبر WiseService،
     *    تسجّل الحدث، ثم توجّهه إلى معالج تغيير حالة التحويل.
     * 
     * 📥 المدخلات:
     *    - $payload: string ← جسم الـ webhook الخام (JSON)
     *    - $headers: array ← هيدرات HTTP (يجب أن تحتوي على X-Wise-Signature)
     * 
     * 📤 المخرجات:
     *    - array ← ['status' => 'ok'] عند النجاح
     * 
     * ❌ الاستثناءات:
     *    - WebhookException ← إذا كان التوقيع فارغاً أو غير صالح
     * 
     * 🔗 الاعتماديات:
     *    - WiseService::verifyWebhook ← التحقق من توقيع JWT
     *    - logWebhook ← تسجيل الحدث مع تنظيف البيانات
     * ──────────────────────────────────────────────────────────
     */
    public function handle(string $payload, array $headers): array
    {
        $signature = $this->extractHeader($headers, 'X-Wise-Signature');

        $service = new WiseService($this->gateway->config ?? []);
        $event = $service->verifyWebhook($payload, $signature);

        if (empty($event)) {
            throw new PaymentException('Invalid signature');
        }

        $this->logWebhook($event['event_type'] ?? '', $payload);

        match ($event['event_type'] ?? '') {
            'transfer.state.change' => $this->handleTransferCompleted($event),
            default => null,
        };

        return ['status' => 'ok'];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 الدالة: handleTransferCompleted
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة حدث transfer.state.change من Wise عند اكتمال التحويل.
     *    تبحث عن التبرع المطابق بالـ transaction_id، تتحقق من الحالة
     *    والمبلغ، ثم تحديث التبرع إلى completed.
     * 
     * 📥 المدخلات:
     *    - $event: array ← بيانات حدث تغيير حالة التحويل من Wise
     * 
     * 🔗 الاعتماديات:
     *    - IdempotencyHelper::checkAndMark ← منع تكرار الحدث
     *    - findDonationByTransactionId ← البحث عن التبرع
     *    - isDonationPending ← التحقق من الحالة
     *    - verifyAmount ← مقارنة المبلغ
     *    - completeDonation ← إتمام التبرع
     * ──────────────────────────────────────────────────────────
     */
    private function handleTransferCompleted(array $event): void
    {
        $transactionId = $event['data']['id'] ?? '';

        if (empty($transactionId)) {
            Log::warning('Wise webhook: missing transfer id');
            return;
        }

        $donation = $this->findDonationByTransactionId($transactionId);
        if (! $donation) {
            Log::info('Wise webhook: unmatched transfer, requires manual confirmation', [
                'transfer_id' => $transactionId,
            ]);
            return;
        }

        if (! $this->isDonationPending($donation)) {
            return;
        }

        $webhookAmount = (float) ($event['data']['amount'] ?? 0);
        if (! $this->verifyAmount($webhookAmount, (float) $donation->amount)) {
            return;
        }

        $this->completeDonation($donation);
    }
}
