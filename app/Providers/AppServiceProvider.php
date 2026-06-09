<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Cryptocurrency;
use App\Models\Donation;
use App\Models\Faq;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Models\QuickAction;
use App\Models\SiteSetting;
use App\Models\Slider;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Testimonial;
use App\Observers\DonationObserver;
use App\Observers\HomeCacheObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Model::preventLazyLoading(! $this->app->isProduction());

        Donation::observe(DonationObserver::class);

        $homeModels = [
            Slider::class, QuickAction::class, Statistic::class,
            Project::class, Post::class, Program::class, Story::class,
            Campaign::class, PaymentMethod::class, Cryptocurrency::class,
            Faq::class, Testimonial::class,
        ];
        foreach ($homeModels as $model) {
            $model::observe(HomeCacheObserver::class);
        }

        $rate = fn ($perMinute) => app()->environment('testing') ? 999 : $perMinute;
        RateLimiter::for('donations', fn (Request $request) => Limit::perMinute($rate(10))->by($request->ip()));
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute($rate(5))->by($request->ip()));
        RateLimiter::for('newsletter', fn (Request $request) => Limit::perMinute($rate(3))->by($request->ip()));
        RateLimiter::for('volunteer', fn (Request $request) => Limit::perMinute($rate(5))->by($request->ip()));
        RateLimiter::for('donor_register', fn (Request $request) => Limit::perMinute(app()->environment('testing') ? 100 : 3)->by($request->ip()));
        RateLimiter::for('donor_login', fn (Request $request) => Limit::perMinute(app()->environment('testing') ? 100 : 10)->by($request->input('email').'|'.$request->ip()));

        Gate::define('viewPulse', fn ($user) => $user->hasRole('super_admin'));

        view()->composer('*', function ($view) {
            if (! $view->offsetExists('settings')) {
                $view->with('settings', Cache::remember('site_settings', 3600, fn () => SiteSetting::current()));
            }
        });
    }
}
