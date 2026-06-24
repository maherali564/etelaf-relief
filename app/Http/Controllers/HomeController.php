<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Models\Donation;
use App\Models\PaymentMethod;
use App\Models\Program;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 الكونترولر: HomeController
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    عرض الصفحة الرئيسية للمنصة محمّلة بكل البيانات اللازمة:
 *    الإعدادات، الشرائح المتحركة، الإحصائيات، المشاريع،
 *    الحملات، قصص النجاح، طرق الدفع، العملات الرقمية، إلخ.
 *    كل البيانات تُخزَّن مؤقتاً (Cache) لتحسين الأداء.
 * 
 * 📋 المسارات التي يعالجها:
 *    GET /{locale}  ← index()
 * 
 * 🔗 الاعتماديات:
 *    - 18 نموذجاً (Model) من App\Models
 *    - Illuminate\Support\Facades\Cache للتخزين المؤقت
 * 
 * ⚠️ ملاحظات:
 *    - مدة التخزين المؤقت الافتراضية = 3600 ثانية (ساعة واحدة)
 *    - آخر التبرعات تُخزَّن لمدة 300 ثانية فقط (تحديث أسرع)
 * ──────────────────────────────────────────────────────────────
 */
class HomeController extends Controller
{
    /**
     * مدة التخزين المؤقت بالثواني (3600 = ساعة واحدة).
     */
    private const CACHE_TTL = 3600;

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: index
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تجميع وعرض جميع بيانات الصفحة الرئيسية — الإعدادات、
     *    الشرائح、الإحصائيات、المشاريع、الحملات、المنشورات、
     *    قصص النجاح、البرامج、القصص、التبرعات、طرق الدفع、
     *    العملات الرقمية、الأسئلة الشائعة、شهادات المستفيدين.
     *    كل مقطع يُخزَّن في Cache بشكل منفصل.
     * 
     * 📥 المدخلات:
     *    لا يوجد معاملات — يستخدم اللغة الحالية من التطبيق
     * 
     * 📤 المخرجات:
     *    - View ← عرض home مع 17 متغيراً من البيانات
     * 
     * 🔗 الاعتماديات:
     *    - SiteSetting::current() ← إعدادات الموقع الحالية
     *    - Slider::active() ← الشرائح النشطة
     *    - QuickAction::active() ← الإجراءات السريعة
     *    - Statistic::active() ← الإحصائيات (نوعين)
     *    - Project::active() ← المشاريع النشطة
     *    - Post (TYPE_ANNOUNCEMENT, TYPE_SUCCESS_STORY)
     *    - Program::active() ← البرامج النشطة
     *    - Story::active() ← القصص النشطة
     *    - Donation::completed() ← التبرعات المكتملة
     *    - Campaign::active() ← الحملات النشطة
     *    - PaymentMethod::with('gateway') ← طرق الدفع
     *    - Cryptocurrency::with('networks') ← العملات الرقمية
     *    - Faq::active() ← الأسئلة الشائعة
     *    - Testimonial::active() ← شهادات المستفيدين
     * 
     * ⚠️ ملاحظات:
     *    - جميع الاستعلامات مخزنة مؤقتاً باستخدام Cache::remember
     *    - المفاتيح تتبع نمط "home.{section_name}"
     *    - lastest_donations لها TTL أقل (300s) لتحديث أسرع
     *    - 50 تبرعاً آخر في الصفحة الرئيسية
     * ──────────────────────────────────────────────────────────────
     */
    public function index(): View
    {
        return view('home', [
            'settings' => SiteSetting::current(),
            'statistics' => [
                'humanitarian' => Cache::remember('home.humanitarian_stats', self::CACHE_TTL, fn () => Statistic::active()->ofType(Statistic::TYPE_HUMANITARIAN)->get()),
                'achievements' => Cache::remember('home.achievement_stats', self::CACHE_TTL, fn () => Statistic::active()->ofType(Statistic::TYPE_ACHIEVEMENT)->get()),
            ],
            'projects' => Cache::remember('home.projects', self::CACHE_TTL, fn () => Project::active()->get()),
            'programs' => Cache::remember('home.programs', self::CACHE_TTL, fn () => Program::active()->get()),
            'stories' => Cache::remember('home.stories', self::CACHE_TTL, fn () => Story::active()->limit(3)->get()),
            'latestDonations' => Cache::remember('home.latest_donations', 300, fn () => Donation::with(['project', 'story'])->completed()->latest()->limit(50)->get()),
            'paymentMethods' => Cache::remember('home.payment_methods', self::CACHE_TTL, fn () => PaymentMethod::with('gateway')->active()->get()),
            'cryptocurrencies' => Cache::remember('home.cryptocurrencies', self::CACHE_TTL, fn () => Cryptocurrency::with('networks')->active()->get()),
            'testimonials' => Cache::remember('home.testimonials', self::CACHE_TTL, fn () => Testimonial::active()->latest()->get()),
        ]);
    }
}
