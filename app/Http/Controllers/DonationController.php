<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonationStoreRequest;
use App\Models\Donation;
use App\Models\Project;
use App\Services\DonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 الكونترولر: DonationController
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    إدارة عملية التبرع بالكامل — عرض صفحات التبرع للمشاريع、
 *    المنشورات والقصص، وعرض جدار المتبرعين، ومعالجة تقديم التبرع
 *    عبر خدمة DonationService.
 * 
 * 📋 المسارات التي يعالجها:
 *    GET  /{locale}/donor-wall              ← donorWall()
 *    POST /{locale}/donate                  ← store()
 * 
 * 🔗 الاعتماديات:
 *    - DonationService ← معالجة منطق التبرع (إنشاء، دفع، إشعارات)
 *    - DonationStoreRequest ← التحقق من صحة بيانات التبرع
 * ──────────────────────────────────────────────────────────────
 */
class DonationController extends Controller
{
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: __construct
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    حقن خدمة التبرع DonationService عبر الـ constructor.
     * 
     * 📥 المدخلات:
     *    - $donationService: DonationService ← خدمة منطق التبرع
     * 
     * 📤 المخرجات:
     *    - void
     * ──────────────────────────────────────────────────────────────
     */
    public function __construct(
        private readonly DonationService $donationService
    ) {}

    public function index(string $locale): View
    {
        $data = $this->donationService->loadDonationPageData();

        return view('donate.index', [...$data]);
    }
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: donorWall
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض جدار المتبرعين — يعرض آخر التبرعات المكتملة مع
     *    إحصائيات سريعة (إجمالي المبلغ، عدد المتبرعين).
     * 
     * 📥 المدخلات:
     *    - $locale: string ← رمز اللغة
     * 
     * 📤 المخرجات:
     *    - View ← عرض donor-wall مع التبرعات والإحصائيات
     * 
     * 🔗 الاعتماديات:
     *    - Donation (Model) ← جلب التبرعات المكتملة
     *    - Cache ← تخزين الإحصائيات لمدة 5 دقائق
     * 
     * ⚠️ ملاحظات:
     *    - يستخدم paginate(50) لعرض 50 تبرع لكل صفحة
     *    - إحصائيات الجدار مخزنة مؤقتاً في Cache لمدة 300 ثانية
     * ──────────────────────────────────────────────────────────────
     */
    public function donorWall(string $locale): View
    {
        $donations = Donation::with(['project', 'story', 'campaign'])->completed()->latest()->paginate(50);

        $wallStats = Cache::remember('donor_wall_stats', 300, function () {
            return [
                'totalRaised' => Donation::completed()->sum('amount'),
                'totalDonors' => Donation::completed()->select('email')->distinct()->count(),
            ];
        });
        $totalRaised = $wallStats['totalRaised'];
        $totalDonors = $wallStats['totalDonors'];

        return view('donor-wall', compact('donations', 'totalRaised', 'totalDonors'));
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: store
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة تقديم نموذج التبرع — إنشاء التبرع، بدء عملية الدفع
     *    إن أمكن (تحويل إلى بوابة دفع أو عرض تعليمات)، أو إرسال
     *    إشعار التأكيد.
     * 
     * 📥 المدخلات:
     *    - $request: DonationStoreRequest ← بيانات التبرع المُحلّلة
     * 
     * 📤 المخرجات:
     *    - RedirectResponse ← إما تحويل إلى بوابة الدفع الخارجية،
     *      أو عرض تعليمات الدفع، أو العودة مع رسالة نجاح/خطأ
     * 
     * 🔗 الاعتماديات:
     *    - DonationService::processDonation() ← إنشاء التبرع
     *    - DonationService::initiatePayment() ← بدء الدفع
     *    - DonationService::sendConfirmationEmail() ← إرسال التأكيد
     *    - DonationService::isOfflinePaymentMethod() ← التحقق من
     *      طريقة دفع غير متصلة (يدوية)
     * 
     * ⚠️ ملاحظات:
     *    - يستخدم try-catch لـ RuntimeException
     *    - يسجل الأخطاء في Log مع التتبع الكامل
     *    - في حالة الدفع اليدوي (offline) يوجه إلى confirm
     *    - في حالة الدفع العادي مع تعليمات يوجه إلى instructions
     *    - إذا لم توجد payment_method_id يرسل تأكيداً فورياً
     * ──────────────────────────────────────────────────────────────
     */
    public function store(DonationStoreRequest $request): RedirectResponse
    {
        try {
            $donation = $this->donationService->processDonation($request->validated());

            if ($donation->payment_method_id) {
                $result = $this->donationService->initiatePayment($donation);

                if ($result && $result['type'] === 'redirect' && ! empty($result['url'])) {
                    return redirect()->away($result['url']);
                }

                if ($result && $result['type'] === 'instructions') {
                    $token = $donation->idempotency_key;

                    if ($this->isOfflinePayment($donation)) {
                        return redirect()->route('payment.confirm', [
                            'locale' => $donation->locale,
                            'donation' => $donation->id,
                            'token' => $token,
                        ]);
                    }

                    return redirect()->route('payment.instructions', [
                        'locale' => $donation->locale,
                        'donation' => $donation->id,
                        'token' => $token,
                    ]);
                }
            }

            $this->donationService->sendConfirmationEmail($donation);

            return back()->with('success', __('common.donation_success'));
        } catch (\RuntimeException $e) {
            Log::error('Donation processing failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('common.error_occurred'));
        }
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: isOfflinePayment
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق مما إذا كانت طريقة الدفع المحددة هي طريقة دفع يدوي
     *    (offline) مثل التحويل البنكي أو الإيداع.
     * 
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كانت طريقة الدفع يدوية، false عدا ذلك
     * 
     * 🔗 الاعتماديات:
     *    - DonationService::isOfflinePaymentMethod()
     * 
     * ⚠️ ملاحظات:
     *    - دالة خاصة (private) تستخدم داخلياً فقط
     * ──────────────────────────────────────────────────────────────
     */
    private function isOfflinePayment(Donation $donation): bool
    {
        return $this->donationService->isOfflinePaymentMethod($donation);
    }
}
