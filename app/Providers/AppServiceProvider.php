<?php

namespace App\Providers;

use App\Models\Cryptocurrency;
use App\Models\Donation;
use App\Models\PaymentMethod;
use App\Models\Page;
use App\Models\Program;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Testimonial;
use App\Observers\DonationObserver;
use App\Observers\HomeCacheObserver;
use App\Observers\TranslationObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * مزود الخدمات الرئيسي للتطبيق (App Service Provider)
 *
 * مسؤول عن تهيئة الخدمات الأساسية للتطبيق عند بدء التشغيل، ويشمل ذلك:
 * - التحقق من إعدادات PHP الأمنية في بيئة الإنتاج
 * - التأكد من وجود مفاتيح بوابات الدفع الأساسية
 * - تسجيل مراقبي الأحداث (Observers) للنماذج المختلفة
 * - تعريف محددات معدل الطلب (Rate Limiters) للمسارات العامة
 * - إعداد بوابات الصلاحيات (Gates) والأدوار
 * - مشاركة إعدادات الموقع (Site Settings) مع جميع القوالب
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * تسجيل أي خدمات حاوية (Container Bindings) في هذا الأسلوب.
     *
     * لا تقوم بأي عملية تسجيل حالياً، لكنها متاحة للإضافات المستقبلية
     * مثل ربط واجهات (Interfaces) بتطبيقات (Implementations) محددة.
     */
    public function register(): void
    {
        //
    }

    /**
     * تشغيل خدمات التطبيق بعد تسجيل جميع المزودين.
     *
     * في بيئة الإنتاج:
     * - تفحص إعدادات PHP الخطرة (expose_php، allow_url_fopen) وتسجل تحذيراً
     * - تتحقق من وجود مفاتيح الدفع الأساسية (Stripe، PayPal) وتسجل تحذيراً عند فقدانها
     *
     * بالإضافة إلى ذلك تقوم بما يلي:
     * - تعيين الحد الأقصى الافتراضي لطول السلاسل النصية في قاعدة البيانات
     * - منع التحميل الكسول (Lazy Loading) في بيئة غير الإنتاج
     * - تسجيل مراقب التبرعات (DonationObserver)
     * - تسجيل مراقب كاش الصفحة الرئيسية (HomeCacheObserver) لـ 12 نموذجاً
     * - تعريف محددات معدل الطلب (Rate Limiters) للتبرعات، التواصل، النشرة البريدية، التطوع، تسجيل ودخول المتبرعين
     * - تحديد صلاحية الوصول إلى لوحة Pulse للمشرف العام فقط
     * - مشاركة إعدادات الموقع المخزنة مؤقتاً مع جميع القوالب
     */
    public function boot(): void
    {
        if ($this->app->isProduction()) {
            $unsafe = [];
            if (ini_get('expose_php')) {
                $unsafe[] = 'expose_php';
            }
            if (ini_get('allow_url_fopen')) {
                $unsafe[] = 'allow_url_fopen';
            }
            if ($unsafe) {
                Log::warning('PHP ini settings security risk: '.implode(', ', $unsafe).' should be Off in production.');
            }

            $required = ['STRIPE_KEY', 'STRIPE_SECRET', 'STRIPE_WEBHOOK_SECRET', 'PAYPAL_CLIENT_ID', 'PAYPAL_SECRET'];
            foreach ($required as $key) {
                if (empty(env($key))) {
                    Log::warning("Missing required config: {$key} is empty in production.");
                }
            }
        }

        Schema::defaultStringLength(191);

        Model::preventLazyLoading(! $this->app->isProduction());

        Donation::observe(DonationObserver::class);

        foreach ([Project::class, Story::class, Page::class] as $model) {
            $model::observe(TranslationObserver::class);
        }

        $homeModels = [
            Statistic::class,
            Project::class, Program::class, Story::class,
            PaymentMethod::class, Cryptocurrency::class,
            Testimonial::class,
        ];
        foreach ($homeModels as $model) {
            $model::observe(HomeCacheObserver::class);
        }

        $rate = fn ($perMinute) => app()->environment('testing') ? 999 : $perMinute;
        RateLimiter::for('donations', fn (Request $request) => Limit::perMinute($rate(5))->by($request->input('email', $request->ip())));
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute($rate(5))->by($request->ip()));
        RateLimiter::for('volunteer', fn (Request $request) => Limit::perMinute($rate(5))->by($request->ip()));
        RateLimiter::for('donor_register', fn (Request $request) => Limit::perMinute(app()->environment('testing') ? 100 : 3)->by($request->ip()));
        RateLimiter::for('donor_login', fn (Request $request) => Limit::perMinute(app()->environment('testing') ? 100 : 10)->by($request->input('email').'|'.$request->ip()));

        Gate::before(fn ($user, $ability) => $user->hasRole('super_admin') ? true : null);

        Gate::define('viewPulse', fn ($user) => $user->hasRole('super_admin'));

        view()->composer('*', function ($view) {
            if (! $view->offsetExists('settings')) {
                $view->with('settings', Cache::remember('site_settings', 3600, fn () => SiteSetting::current()));
            }
        });

        try {
            $setting = SiteSetting::current();
            if ($locales = $setting->enabled_locales) {
                config(['app.supported_locales' => $locales]);
            }
        } catch (\Throwable $e) {
            // table may not exist yet (first migration)
        }
    }
}
