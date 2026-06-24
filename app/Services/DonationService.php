<?php

/**
 * ──────────────────────────────────────────────────────────────
 * 🎯 الخدمة: DonationService
 * ──────────────────────────────────────────────────────────────
 * 📌 الغرض:
 *    إدارة دورة حياة التبرع بالكامل — من إنشاء التبرع،
 *    إلى بدء عملية الدفع، وإرسال إيميل التأكيد.
 * 
 * 🔗 الاعتماديات:
 *    - PaymentService ← لبدء عملية الدفع عبر البوابة المناسبة
 *    - IdempotencyHelper ← لتوليد مفتاح فريد لمنع التكرار
 *    - DonationConfirmation (Mailable) ← لإيميل التأكيد
 * 
 * 📦 المسؤوليات:
 *    1. loadDonationPageData() ← تحميل بيانات صفحة التبرع مع caching
 *    2. processDonation() ← إنشاء سجل تبرع جديد في قاعدة البيانات
 *    3. initiatePayment() ← بدء الدفع مع 3 محاولات (retry)
 *    4. isOfflinePaymentMethod() ← التحقق من طرق الدفع غير المباشرة
 *    5. sendConfirmationEmail() ← إرسال إيميل تأكيد للمتبرع
 * ──────────────────────────────────────────────────────────────
 */

namespace App\Services;

use App\Mail\DonationConfirmation;
use App\Models\Cryptocurrency;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\Story;
use App\Services\Payment\IdempotencyHelper;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class DonationService
{
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: loadDonationPageData
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تحميل جميع البيانات اللازمة لعرض صفحة التبرع (بوابات الدفع،
     *    الحملات، المشاريع، القصص، العملات الرقمية، وآخر التبرعات)
     *    مع تخزينها في_cache لمدة 5 دقائق لتقليل استعلامات DB.
     * 
     * 📥 المدخلات:
     *    - $projectId: int|null ← معرف المشروع المحدد (لتصفية التبرعات)
     *    - $postId: int|null ← معرف المنشور المحدد
     *    - $storyId: int|null ← معرف القصة المحددة
     * 
     * 📤 المخرجات:
     *    - array ← مصفوفة تحتوي على: paymentMethods, campaigns,
     *      projects, stories, cryptocurrencies, donations
     * 
     * 🔗 الاعتماديات:
     *    - PaymentMethod (مع gateway) ← بوابات الدفع النشطة
     *    - Campaign ← الحملات النشطة
     *    - Project ← المشاريع النشطة
     *    - Cryptocurrency (مع networks) ← العملات الرقمية النشطة
     *    - Donation ← آخر 20 تبرع مكتمل
     * 
     * ⚠️ ملاحظات:
     *    - Cache key يعتمد على hash من المعاملات لتجنب التصادم
     *    - TTL = 300 ثانية (5 دقائق)
     *    - Cache لا يُمسح عند تحديث التبرعات ← يحتاج إصلاح
     * ──────────────────────────────────────────────────────────────
     */
    public function loadDonationPageData(?int $projectId = null, ?int $storyId = null): array
    {
        $cacheKey = 'donation_page_data_'.hash('sha256', serialize(compact('projectId', 'storyId')));

        return Cache::remember($cacheKey, 300, function () use ($projectId, $storyId) {
            return [
                'paymentMethods' => PaymentMethod::with('gateway')->active()->get(),
                'projects' => Project::active()->get(),
                'stories' => Story::active()->get(),
                'cryptocurrencies' => Cryptocurrency::with('networks')->active()->get(),
                'donations' => Donation::completed()
                    ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
                    ->when($storyId, fn ($q) => $q->where('story_id', $storyId))
                    ->latest()
                    ->limit(20)
                    ->get(),
            ];
        });
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: processDonation
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إنشاء سجل تبرع جديد في قاعدة البيانات مع تعيين القيم
     *    الافتراضية (العملة، الحالة، المفتاح الفريد) داخل
     *    transaction لضمان atomicity.
     * 
     * 📥 المدخلات:
     *    - $validated: array ← البيانات المُتحقق منها من Form Request
     *      (تحتوي على: email, amount, payment_method_id, campaign_id, ...)
     * 
     * 📤 المخرجات:
     *    - Donation ← كائن التبرع المُنشأ مع البيانات الكاملة
     * 
     * 🔗 الاعتماديات:
     *    - Donor ← البحث عن المتبرع بواسطة البريد الإلكتروني
     *    - PaymentMethod (+ gateway) ← لتحديد driver المدفوعات
     *    - IdempotencyHelper::generateKey() ← توليد مفتاح فريد
     * 
     * 📌 مثال:
     *    $donation = $service->processDonation($request->validated());
     * 
     * ⚠️ ملاحظات:
     *    - العملة ثابتة على USD حالياً ← قد تحتاج للتغيير
     *    - الحالة تبدأ بـ 'pending' ثم تتغير عبر webhook
     *    - idempotency_key يُستخدم كـ token للتحقق من الوصول
     * ──────────────────────────────────────────────────────────────
     */
    public function processDonation(array $validated): Donation
    {
        return DB::transaction(function () use ($validated) {
            $donor = Donor::where('email', $validated['email'])->first();

            $driver = PaymentMethod::find($validated['payment_method_id'])?->gateway?->driver ?? 'unknown';

            $donation = new Donation();
            $donation->fill($validated);
            $donation->donor_id = $donor?->id;
            $donation->payment_method_id = $validated['payment_method_id'] ?? null;
            $donation->project_id = $validated['project_id'] ?? null;
            $donation->story_id = $validated['story_id'] ?? null;
            $donation->cryptocurrency_id = $validated['cryptocurrency_id'] ?? null;
            $donation->crypto_network_id = $validated['crypto_network_id'] ?? null;
            $donation->transaction_id = $validated['transaction_id'] ?? ('TXN-'.strtoupper(Str::random(16)));
            $donation->currency = 'USD';
            $donation->status = 'pending';
            $donation->locale = app()->getLocale();
            $donation->donated_at = now();
            $donation->idempotency_key = IdempotencyHelper::generateKey($driver);
            $donation->save();

            return $donation;
        });
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: initiatePayment
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    بدء عملية الدفع مع 3 محاولات (retry) مع exponential
     *    backoff. كل محاولة تفشل تسجل الخطأ وتحدّث حقل
     *    last_error في التبرع.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد تحصيل قيمته
     * 
     * 📤 المخرجات:
     *    - array|null ← نتيجة الدفع:
     *      - ['type' => 'redirect', 'url' => '...'] ← لتحويل المستخدم
     *      - ['type' => 'instructions', 'data' => [...]] ← لتعليمات الدفع
     *      - null ← إذا لم يوجد payment_method_id
     * 
     * 🔗 الاعتماديات:
     *    - PaymentService::fromDonation() ← إنشاء الخدمة المناسبة
     *    - PaymentService::initPayment() ← بدء الدفع
     * 
     * ⚠️ ملاحظات:
     *    - Retry: تأخير 0.5 ثانية، 1 ثانية، 1.5 ثانية
     *    - بعد 3 محاولات فاشلة → throws RuntimeException
     *    - القيمة الافتراضية للمحاولات = 3
     * ──────────────────────────────────────────────────────────────
     */
    public function initiatePayment(Donation $donation): ?array
    {
        if (! $donation->payment_method_id) {
            return null;
        }

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $payment = PaymentService::fromDonation($donation);
                $result = $payment->initPayment($donation);

                if ($attempt > 1) {
                    Log::info('Payment retry succeeded', [
                        'donation_id' => $donation->id,
                        'attempt' => $attempt,
                    ]);
                }

                return $result;
            } catch (RuntimeException $e) {
                Log::error('Payment initiation failed', [
                    'donation_id' => $donation->id,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                $donation->update([
                    'payment_attempts' => $attempt,
                    'last_error' => $e->getMessage(),
                    'last_attempt_at' => now(),
                ]);

                if ($attempt < $maxAttempts) {
                    usleep($attempt * 500000);
                }
            }
        }

        throw new RuntimeException('فشلت محاولات الدفع بعد '.$maxAttempts.' محاولات');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: isOfflinePaymentMethod
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق مما إذا كانت طريقة الدفع تتطلب تحويلاً يدوياً
     *    (غير متصل) بدلاً من الدفع الفوري عبر Stripe/PayPal.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد فحصه
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كانت طريقة الدفع غير متصلة
     * 
     * 🔗 الاعتماديات:
     *    - Donation->paymentMethod->gateway->driver
     * 
     * 📌 مثال:
     *    if ($service->isOfflinePaymentMethod($donation)) { ... }
     * ──────────────────────────────────────────────────────────────
     */
    public function isOfflinePaymentMethod(Donation $donation): bool
    {
        $driver = $donation->paymentMethod?->gateway?->driver ?? '';

        return in_array($driver, ['bank_transfer', 'wise', 'crypto']);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: sendConfirmationEmail
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرسال إيميل تأكيد التبرع للمتبرع مع حالة "قيد المراجعة"
     *    (خاصة للتبرعات غير المباشرة: تحويل بنكي، Wise، عملات رقمية).
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← التبرع المراد تأكيده
     * 
     * 📤 المخرجات:
     *    - void → لا شيء (الإيميل يُرسل بشكل غير متزامن)
     * 
     * 🔗 الاعتماديات:
     *    - DonationConfirmation (Mailable) ← قالب الإيميل
     *    - Mail::to()->send() ← إرسال الإيميل
     * 
     * ⚠️ ملاحظات:
     *    - أي خطأ في الإرسال يُسجل في Log فقط (لا يمنع العملية)
     *    - الحالة 'under_review' تُستخدم للتبرعات اليدوية
     * ──────────────────────────────────────────────────────────────
     */
    public function sendConfirmationEmail(Donation $donation): void
    {
        try {
            Mail::to($donation->email)->send(new DonationConfirmation($donation, 'under_review'));
        } catch (\Exception $e) {
            Log::error('Donation confirmation email failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
