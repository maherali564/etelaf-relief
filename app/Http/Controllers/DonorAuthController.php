<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonorLoginRequest;
use App\Http\Requests\DonorRegisterRequest;
use App\Models\Donation;
use App\Models\Donor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 الكونترولر: DonorAuthController
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    إدارة مصادقة المتبرعين (Donors) — تسجيل、دخول、خروج、
 *    ولوحة تحكم المتبرع. يستخدم حارس المصادقة 'donor' مع
 *    نظام Rate Limiting لمنع هجمات القوة العمياء.
 * 
 * 📋 المسارات التي يعالجها:
 *    GET  /{locale}/donor/register    ← showRegister()
 *    POST /{locale}/donor/register    ← register()
 *    GET  /{locale}/donor/login       ← showLogin()
 *    POST /{locale}/donor/login       ← login()
 *    POST /{locale}/donor/logout      ← logout()
 *    GET  /{locale}/donor/dashboard   ← dashboard()
 * 
 * 🔗 الاعتماديات:
 *    - Donor (Model) ← إنشاء وإدارة المتبرعين
 *    - Donation (Model) ← ربط التبرعات بالمتبرع
 *    - DonorRegisterRequest / DonorLoginRequest ← التحقق من الصحة
 *    - Auth (guard: donor) ← المصادقة
 *    - RateLimiter ← منع كثرة المحاولات (5 محاولات/دقيقة)
 * ──────────────────────────────────────────────────────────────
 */
class DonorAuthController extends Controller
{
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: showRegister
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض نموذج تسجيل متبرع جديد.
     * 
     * 📥 المدخلات:
     *    لا يوجد معاملات
     * 
     * 📤 المخرجات:
     *    - View ← عرض donor.register مع اللغة الحالية
     * 
     * ⚠️ ملاحظات:
     *    - يمرر currentLocale لدعم تعدد اللغات في النموذج
     * ──────────────────────────────────────────────────────────────
     */
    public function showRegister()
    {
        return view('donor.register', ['currentLocale' => app()->getLocale()]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: register
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة تسجيل متبرع جديد — إنشاء السجل、تشفير كلمة المرور、
     *    ربط التبرعات السابقة بنفس البريد الإلكتروني、وتسجيل الدخول
     *    تلقائياً بعد التسجيل.
     * 
     * 📥 المدخلات:
     *    - $request: DonorRegisterRequest ← بيانات التسجيل المُحلّلة
     * 
     * 📤 المخرجات:
     *    - RedirectResponse ← تحويل إلى لوحة تحكم المتبرع
     * 
     * 🔗 الاعتماديات:
     *    - Donor::create() ← إنشاء المتبرع
     *    - Hash::make() ← تشفير كلمة المرور
     *    - Donation::where('email') ← ربط التبرعات السابقة
     *    - Auth::guard('donor')->login() ← تسجيل الدخول
     * 
     * ⚠️ ملاحظات:
     *    - يبحث عن تبرعات سابقة بنفس البريد ويربطها بالمتبرع الجديد
     * ──────────────────────────────────────────────────────────────
     */
    public function register(DonorRegisterRequest $request)
    {
        $donor = Donor::create([
            ...$request->validated(),
            'password' => Hash::make($request->input('password')),
        ]);

        Donation::where('email', $donor->email)->whereNull('donor_id')->update(['donor_id' => $donor->id]);

        Auth::guard('donor')->login($donor);

        return redirect()->route('donor.dashboard', ['locale' => app()->getLocale()]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: showLogin
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض نموذج دخول المتبرع.
     * 
     * 📥 المدخلات:
     *    لا يوجد معاملات
     * 
     * 📤 المخرجات:
     *    - View ← عرض donor.login مع اللغة الحالية
     * ──────────────────────────────────────────────────────────────
     */
    public function showLogin()
    {
        return view('donor.login', ['currentLocale' => app()->getLocale()]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: login
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    معالجة طلب دخول المتبرع مع حماية Rate Limiting —
     *    يسمح بـ 5 محاولات فاشلة كحد أقصى قبل القفل لمدة 60 ثانية.
     * 
     * 📥 المدخلات:
     *    - $request: DonorLoginRequest ← بيانات الدخول المُحلّلة
     * 
     * 📤 المخرجات:
     *    - RedirectResponse ← إما تحويل إلى لوحة التحكم (نجاح)
     *      أو العودة مع رسالة خطأ (فشل) أو رسالة throttle (قفل)
     * 
     * 🔗 الاعتماديات:
     *    - RateLimiter ← منع تجاوز 5 محاولات فاشلة
     *    - Auth::guard('donor')->attempt() ← محاولة المصادقة
     * 
     * ⚠️ ملاحظات:
     *    - مفتاح RateLimiter: "donor-login:{ip_address}"
     *    - 5 محاولات كحد أقصى قبل القفل
     *    - مدة القفل: 60 ثانية
     *    - بعد النجاح: مسح محاولات RateLimiter
     *    - بعد الفشل: تسجيل محاولة جديدة في RateLimiter
     *    - يستخدم intended() لتوجيه المستخدم للصفحة التي كان يحاول
     *      الوصول إليها قبل الدخول
     * ──────────────────────────────────────────────────────────────
     */
    public function login(DonorLoginRequest $request)
    {
        $key = 'donor-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => __('donor.throttle', ['seconds' => $seconds])]);
        }

        if (Auth::guard('donor')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            RateLimiter::clear($key);

            return redirect()->intended(
                route('donor.dashboard', ['locale' => app()->getLocale()])
            );
        }

        RateLimiter::hit($key, 60);

        return back()->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => __('donor.login_error')]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: logout
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسجيل خروج المتبرع — إبطال الجلسة وتجديد رمز CSRF.
     * 
     * 📥 المدخلات:
     *    لا يوجد معاملات
     * 
     * 📤 المخرجات:
     *    - RedirectResponse ← تحويل إلى الصفحة الرئيسية
     * 
     * 🔗 الاعتماديات:
     *    - Auth::guard('donor')->logout()
     *    - session()->invalidate() + regenerateToken()
     * 
     * ⚠️ ملاحظات:
     *    - يبطل الجلسة بالكامل ويعيد生成 رمز CSRF جديد للأمان
     * ──────────────────────────────────────────────────────────────
     */
    public function logout()
    {
        Auth::guard('donor')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home', ['locale' => app()->getLocale()]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: dashboard
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض لوحة تحكم المتبرع — تبرعاته السابقة、إجمالي المبلغ
     *    المتبرع به、عدد التبرعات.
     * 
     * 📥 المدخلات:
     *    لا يوجد معاملات — يستخدم المتبرع المُوثَّق حالياً
     * 
     * 📤 المخرجات:
     *    - View ← عرض donor.dashboard مع بيانات المتبرع
     * 
     * 🔗 الاعتماديات:
     *    - Auth::guard('donor')->user() ← المتبرع الحالي
     *    - Donor->donations() ← تبرعات المتبرع (paginate 20)
     *    - Donor->total_donated ← إجمالي المبلغ
     *    - Donor->donation_count ← عدد التبرعات
     * 
     * ⚠️ ملاحظات:
     *    - يعرض آخر 20 تبرعاً مع ترقيم الصفحات
     * ──────────────────────────────────────────────────────────────
     */
    public function dashboard()
    {
        $donor = Auth::guard('donor')->user();

        return view('donor.dashboard', [
            'donor' => $donor,
            'donations' => $donor->donations()->latest()->paginate(20),
            'totalDonated' => $donor->total_donated,
            'donationCount' => $donor->donation_count,
            'currentLocale' => app()->getLocale(),
        ]);
    }
}
