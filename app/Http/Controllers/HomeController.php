<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const CACHE_TTL = 3600;

    public function index(): View
    {
        return view('home', [
            'settings' => SiteSetting::current(),
            'sliders' => Cache::remember('home.sliders', self::CACHE_TTL, fn () => Slider::active()->get()),
            'quickActions' => Cache::remember('home.quick_actions', self::CACHE_TTL, fn () => QuickAction::active()->get()),
            'achievementStats' => Cache::remember('home.achievement_stats', self::CACHE_TTL, fn () => Statistic::active()->ofType(Statistic::TYPE_ACHIEVEMENT)->get()),
            'humanitarianStats' => Cache::remember('home.humanitarian_stats', self::CACHE_TTL, fn () => Statistic::active()->ofType(Statistic::TYPE_HUMANITARIAN)->get()),
            'projects' => Cache::remember('home.projects', self::CACHE_TTL, fn () => Project::active()->get()),
            'announcements' => Cache::remember('home.announcements', self::CACHE_TTL, fn () => Post::with('campaign')->active()->ofType(Post::TYPE_ANNOUNCEMENT)->published()->limit(3)->get()),
            'successStories' => Cache::remember('home.success_stories', self::CACHE_TTL, fn () => Post::with('campaign')->active()->ofType(Post::TYPE_SUCCESS_STORY)->published()->limit(3)->get()),
            'programs' => Cache::remember('home.programs', self::CACHE_TTL, fn () => Program::active()->get()),
            'stories' => Cache::remember('home.stories', self::CACHE_TTL, fn () => Story::active()->limit(3)->get()),
            'latestDonations' => Cache::remember('home.latest_donations', 300, fn () => Donation::with(['campaign', 'project', 'story'])->completed()->latest()->limit(50)->get()),
            'campaigns' => Cache::remember('home.campaigns', self::CACHE_TTL, fn () => Campaign::active()->get()),
            'paymentMethods' => Cache::remember('home.payment_methods', self::CACHE_TTL, fn () => PaymentMethod::with('gateway')->active()->get()),
            'cryptocurrencies' => Cache::remember('home.cryptocurrencies', self::CACHE_TTL, fn () => Cryptocurrency::with('networks')->active()->get()),
            'faqs' => Cache::remember('home.faqs', self::CACHE_TTL, fn () => Faq::active()->get()),
            'testimonials' => Cache::remember('home.testimonials', self::CACHE_TTL, fn () => Testimonial::active()->latest()->get()),
        ]);
    }
}
