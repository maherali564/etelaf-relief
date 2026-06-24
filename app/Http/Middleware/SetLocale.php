<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * وسيط (Middleware) لتعيين اللغة الحالية للتطبيق.
 *
 * يعمل هذا الوسيط على جميع المسارات العامة التي تحمل بادئة اللغة
 * ({locale} في الرابط). يقوم باستخراج اللغة من الرابط، جلسة المستخدم،
 * أو الإعداد الافتراضي للتطبيق، ثم يضبط اللغة ويشاركها مع جميع
 * القوالب (Blade views) لعرض المحتوى باللغة المناسبة.
 *
 * اللغات المدعومة: العربية (ar)، الإنجليزية (en)، الإسبانية (es)،
 * الإندونيسية (id)، التركية (tr).
 *
 * @package App\Http\Middleware
 */
class SetLocale
{
    /**
     * معالجة الطلب الوارد وتعيين اللغة.
     *
     * 1. يستخرج اللغة من معامل الرابط `locale`، أو من الجلسة،
     *    أو من اللغة الافتراضية في config/app.php
     * 2. يتحقق من أن اللغة مدعومة — إذا لم تكن مدعومة، يستخدم
     *    اللغة البديلة (fallback_locale)
     * 3. يضبط اللغة في التطبيق عبر `App::setLocale()`
     * 4. يحفظ اللغة في الجلسة
     * 5. يشارك متغيرات القالب:
     *    - `currentLocale`: اللغة الحالية
     *    - `supportedLocales`: قائمة اللغات المدعومة
     *    - `localeLabels`: أسماء اللغات المعروضة
     *    - `localeFlags`: أعلام الدول (إيموجي)
     *    - `isRtl`: قيمة منطقية تحدد إذا كانت اللغة عربية (لاتجاه RTL)
     *
     * @param Request $request الطلب الوارد
     * @param Closure $next الدالة التالية في سلسلة الوسائط
     * @return Response الاستجابة النهائية
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['ar', 'en', 'es', 'id', 'tr']);
        $locale = $request->route('locale') ?? session('locale', config('app.locale'));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);
        session(['locale' => $locale]);

        View::share('currentLocale', $locale);
        View::share('supportedLocales', $supported);
        View::share('localeLabels', [
            'ar' => 'العربية',
            'en' => 'English',
            'es' => 'Español',
            'id' => 'Bahasa Indonesia',
            'tr' => 'Türkçe',
        ]);
        View::share('localeFlags', [
            'ar' => '🇸🇦',
            'en' => '🇬🇧',
            'es' => '🇪🇸',
            'id' => '🇮🇩',
            'tr' => '🇹🇷',
        ]);
        View::share('isRtl', $locale === 'ar');

        return $next($request);
    }
}
