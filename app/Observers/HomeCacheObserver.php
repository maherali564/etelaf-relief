<?php

namespace App\Observers;

use App\Models\Campaign;
use App\Models\Cryptocurrency;
use App\Models\Faq;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Models\QuickAction;
use App\Models\Slider;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class HomeCacheObserver
{
    public function saved(mixed $model): void
    {
        $this->clearHomeCache($model);
    }

    public function deleted(mixed $model): void
    {
        $this->clearHomeCache($model);
    }

    private function clearHomeCache(mixed $model): void
    {
        $keys = match (true) {
            $model instanceof Slider => ['home.sliders'],
            $model instanceof QuickAction => ['home.quick_actions'],
            $model instanceof Statistic => ['home.achievement_stats', 'home.humanitarian_stats'],
            $model instanceof Project => ['home.projects'],
            $model instanceof Post => ['home.announcements', 'home.success_stories'],
            $model instanceof Program => ['home.programs'],
            $model instanceof Story => ['home.stories'],
            $model instanceof Campaign => ['home.campaigns'],
            $model instanceof PaymentMethod => ['home.payment_methods'],
            $model instanceof Cryptocurrency => ['home.cryptocurrencies'],
            $model instanceof Faq => ['home.faqs'],
            $model instanceof Testimonial => ['home.testimonials'],
            default => [],
        };

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
