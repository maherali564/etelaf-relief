<?php

namespace App\Services;

use App\Models\CryptoNetwork;
use App\Models\Cryptocurrency;
use App\Models\Donation;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 ConfirmationService
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    خدمة إدارة تأكيدات الدفع للتبرعات التي تتطلب تحويلاً
 *    يدوياً (مثل التحويل البنكي أو Wise). تتولى تحميل بيانات
 *    صفحة التأكيد، التحقق من صحة البوابة، وتقديم إثبات
 *    الدفع (مستند التحويل) للمراجعة من قبل المشرفين.
 *
 * 🔗 الاعتماديات:
 *    - Donation (Model) ← التبرع المراد تأكيده
 *    - PaymentConfirmation (Model) ← سجل إثبات الدفع
 *    - Cryptocurrency (Model) ← معلومات العملات الرقمية
 *    - CryptoNetwork (Model) ← شبكات العملات الرقمية
 *    - Log (Facade) ← تسجيل عمليات التأكيد
 * ──────────────────────────────────────────────────────────────
 */
class ConfirmationService
{
    /**
     * ──────────────────────────────────────────────────────────
     * 📌 loadConfirmationPage
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تجهيز جميع البيانات اللازمة لعرض صفحة تأكيد الدفع
     *    للتبرع. تسترجع معلومات طريقة الدفع، البوابة،
     *    الإعدادات، التعليمات، وفي حالة البوابة الرقمية
     *    (crypto) تجلب قائمة العملات الرقمية والشبكات
     *    المتاحة.
     *
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع المراد تأكيده
     *
     * 📤 المخرجات:
     *    - array ← تحتوي على:
     *        'paymentMethod', 'gateway', 'config', 'instructions',
     *        'driver', 'cryptocurrencies' (nullable),
     *        'selectedNetwork' (nullable)
     *
     * 🔗 الاعتماديات:
     *    - Cryptocurrency::with('networks')->active()->get()
     *      ← جلب العملات النشطة مع الشبكات
     *    - CryptoNetwork::with('cryptocurrency')->find()
     *      ← الشبكة المختارة مسبقاً
     * ──────────────────────────────────────────────────────────
     */
    public function loadConfirmationPage(Donation $donation): array
    {
        $paymentMethod = $donation->paymentMethod;
        $gateway = $paymentMethod?->gateway;

        $cryptocurrencies = null;
        $selectedNetwork = null;
        if ($gateway && $gateway->driver === 'crypto') {
            $cryptocurrencies = Cryptocurrency::with('networks')->active()->get();
            $selectedNetwork = $donation->crypto_network_id
                ? CryptoNetwork::with('cryptocurrency')->find($donation->crypto_network_id)
                : null;
        }

        return [
            'paymentMethod' => $paymentMethod,
            'gateway' => $gateway,
            'config' => $gateway?->config ?? [],
            'instructions' => $paymentMethod?->instructions ?? '',
            'driver' => $gateway?->driver ?? '',
            'cryptocurrencies' => $cryptocurrencies,
            'selectedNetwork' => $selectedNetwork,
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 validateGateway
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن بوابة الدفع المرتبطة بالتبرع تدعم
     *    تدفق التأكيد (أي تتطلب خطوة تأكيد يدوي). البوابات
     *    المدعومة: bank_transfer, wise, crypto.
     *
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع
     *
     * 📤 المخرجات:
     *    - string|null ← اسم مشغل البوابة (driver) إذا كان
     *      مدعوماً، أو null إذا لم يكن مدعوماً
     *
     * 🔗 الاعتماديات:
     *    - Donation::paymentMethod (Relationship) ← العلاقة
     *      مع طريقة الدفع
     * ──────────────────────────────────────────────────────────
     */
    public function validateGateway(Donation $donation): ?string
    {
        $gateway = $donation->paymentMethod?->gateway;
        if (! $gateway || ! in_array($gateway->driver, ['bank_transfer', 'wise', 'crypto'])) {
            return null;
        }

        return $gateway->driver;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 validateStoreGateway
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن بوابة الدفع تدعم تخزين إثبات الدفع
     *    (رفع مستند). البوابات المدعومة: bank_transfer, wise
     *    فقط (crypto لا يحتاج مستند تأكيد).
     *
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع
     *
     * 📤 المخرجات:
     *    - string|null ← اسم مشغل البوابة إذا كان مدعوماً
     *      لتخزين التأكيد، أو null إذا لم يكن
     *
     * 🔗 الاعتماديات:
     *    - Donation::paymentMethod (Relationship)
     * ──────────────────────────────────────────────────────────
     */
    public function validateStoreGateway(Donation $donation): ?string
    {
        $gateway = $donation->paymentMethod?->gateway;
        if (! $gateway || ! in_array($gateway->driver, ['bank_transfer', 'wise'])) {
            return null;
        }

        return $gateway->driver;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 submitConfirmation
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تقديم إثبات دفع (تحويل بنكي) لمراجعة المشرفين.
     *    تنشئ سجلاً جديداً في جدول PaymentConfirmation مع
     *    بيانات التحويل (رقم المرجع، المبلغ، اسم المحول،
     *    تاريخ التحويل، إلخ) وترفق مستند الإثبات إذا وُجد.
     *    تحديث حالة التبرع إلى 'under_review'.
     *
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع المرتبط
     *    - $validated: array ← البيانات المُحقق منها من النموذج:
     *        reference_number, amount, currency, sender_name,
     *        sender_account, transfer_date, notes
     *    - $request: Request|null ← طلب HTTP اختياري لرفع
     *      مستند الإثبات (proof_document)
     *
     * ❌ الاستثناءات:
     *    - ValidationException ← قد ترميها Laravel إذا فشل
     *      التحقق من صحة الملف المرفوع
     *
     * 🔗 الاعتماديات:
     *    - PaymentConfirmation::save() ← حفظ إثبات الدفع
     *    - Donation::update() ← تحديث حالة التبرع
     *    - Log::info() ← تسجيل العملية
     * ──────────────────────────────────────────────────────────
     */
    public function submitConfirmation(Donation $donation, array $validated, ?Request $request = null): void
    {
        $data = [
            'donation_id' => $donation->id,
            'type' => 'bank_transfer',
            'reference_number' => $validated['reference_number'] ?? null,
            'amount' => $validated['amount'] ?? $donation->amount,
            'currency' => $validated['currency'] ?? $donation->currency,
            'sender_name' => $validated['sender_name'] ?? $donation->donor_name,
            'sender_account' => $validated['sender_account'] ?? null,
            'transfer_date' => $validated['transfer_date'] ?? now(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ];

        if ($request && $request->hasFile('proof_document')) {
            $data['proof_document'] = $request->file('proof_document')->store('confirmations', 'private');
        }

        $confirmation = new PaymentConfirmation();
        $confirmation->fill($data);
        $confirmation->donation_id = $donation->id;
        $confirmation->save();

        $donation->update([
            'status' => 'under_review',
            'confirmation_details' => [
                'reference_number' => $data['reference_number'],
                'type' => $data['type'],
                'submitted_at' => now()->toDateTimeString(),
            ],
        ]);

        Log::info('Bank transfer confirmation submitted', [
            'donation_id' => $donation->id,
            'reference' => $data['reference_number'],
        ]);
    }
}
