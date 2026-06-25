<?php

namespace App\Observers;

use App\Models\Cryptocurrency;
use App\Models\PaymentMethod;
use App\Models\Program;
use App\Models\Project;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

/**
 * مراقب كاش الصفحة الرئيسية (Home Cache Observer)
 *
 * يستمع لأحداث الحفظ والحذف على نماذج متعددة تُستخدم في الصفحة الرئيسية
 * ويقوم بمسح مفاتيح الكاش المرتبطة بكل نموذج تلقائياً.
 *
 * هذا يضمن أن أي تغيير في المحتوى (سلايدر، إحصائيات، مشاريع، حملات، إلخ)
 * ينعكس فوراً على الصفحة الرئيسية دون الحاجة لمسح الكاش يدوياً.
 *
 * النماذج المشمولة: Slider، QuickAction، Statistic، Project، Post،
 * Program، Story، PaymentMethod، Cryptocurrency، Testimonial
 */
class HomeCacheObserver
{
    /**
     * يتم تنفيذه بعد حفظ أي نموذج (إنشاء أو تحديث).
     * يقوم بمسح مفاتيح الكاش المرتبطة بالنموذج.
     *
     * @param  mixed  $model  النموذج الذي تم حفظه
     */
    public function saved(mixed $model): void
    {
        $this->clearHomeCache($model);
    }

    /**
     * يتم تنفيذه بعد حذف أي نموذج.
     * يقوم بمسح مفاتيح الكاش المرتبطة بالنموذج.
     *
     * @param  mixed  $model  النموذج الذي تم حذفه
     */
    public function deleted(mixed $model): void
    {
        $this->clearHomeCache($model);
    }

    /**
     * مسح مفاتيح الكاش الخاصة بالصفحة الرئيسية بناءً على نوع النموذج.
     *
     * تستخدم مطابقة النمط (match) لتحديد مفاتيح الكاش المرتبطة بكل نموذج،
     * ثم تقوم بحذفها من الكاش لضمان عرض أحدث البيانات.
     *
     * @param  mixed  $model  النموذج المراد مسح الكاش المرتبط به
     */
    private function clearHomeCache(mixed $model): void
    {
        $keys = match (true) {
            $model instanceof Statistic => ['home.achievement_stats', 'home.humanitarian_stats'],
            $model instanceof Project => ['home.projects', 'gallery_items'],
            $model instanceof Program => ['home.programs'],
            $model instanceof Story => ['home.stories', 'gallery_items'],
            $model instanceof PaymentMethod => ['home.payment_methods'],
            $model instanceof Cryptocurrency => ['home.cryptocurrencies'],
            $model instanceof Testimonial => ['home.testimonials'],
            default => [],
        };

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
