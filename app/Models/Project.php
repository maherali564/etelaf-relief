<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 النموذج: Project (المشروع)
 * ──────────────────────────────────────────────────────────────
 * 🗃️ الجدول: projects
 * 
 * 🎯 الغرض:
 *    يمثل مشروعاً إغاثياً أو تنموياً على المنصة. يحتوي على
 *    محتوى متعدد اللغات (عنوان، وصف، محتوى كامل)، وصور،
 *    وفيديو، ومبلغ مستهدف. يمكن تمييز المشاريع كمميزة
 *    وترتيبها حسب الأولوية.
 * 
 * 📥 الحقول القابلة للتعبئة (fillable):
 *    - slug ← معرف الرابط الفريد
 *    - title, description, content ← العنوان والوصف والمحتوى (متعددة اللغات)
 *    - image, images, video_url ← الصور والفيديو
 *    - goal_amount, raised_amount ← المبلغ المستهدف والمبلغ المجموع
 *    - is_featured ← مشروع مميز؟
 *    - sort_order ← ترتيب العرض
 *    - is_active ← حالة التفعيل
 * 
 * 🌐 الحقول المترجمة (translatable):
 *    - title, description, content
 * 
 * 🔄 الـ Casts:
 *    - is_featured, is_active → boolean
 *    - images → array
 *    - goal_amount, raised_amount → decimal:2
 * 
 * 🔗 العلاقات:
 *    - donations → hasMany Donation
 * 
 * 🔍 النطاقات (Scopes):
 *    - active ← المشاريع النشطة فقط (is_active = true) مرتبة
 *    - featured ← المشاريع المميزة فقط (is_featured = true)
 * 
 * ⚙️ Boot Events:
 *    - saving: توليد slug تلقائياً من العنوان الإنجليزي إن لم يكن موجوداً
 * ──────────────────────────────────────────────────────────────
 */
class Project extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'slug', 'program_id', 'title', 'description', 'content',
        'image', 'images', 'video_url', 'videos', 'video', 'video_thumbnail', 'video_status',
        'goal_amount', 'raised_amount', 'location',
        'start_date', 'end_date',
        'is_featured', 'sort_order', 'is_active',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public array $translatable = ['title', 'description', 'content', 'location'];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'videos' => 'array',
        'goal_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
    ];

    public function scopePendingVideo($query) { return $query->where('video_status', 'pending'); }
    public function scopeProcessingVideo($query) { return $query->where('video_status', 'processing'); }
    public function scopeVideoCompleted($query) { return $query->where('video_status', 'completed'); }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الخاصية المحسوبة: getFirstImageAttribute
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرجاع أول صورة متاحة للمشروع. يفضل الصورة الرئيسية (image)
     *    فإن لم توجد، يعيد أول صورة من معرض الصور (images).
     * 
     * 📤 المخرجات:
     *    - string|null ← رابط الصورة الأولى أو null إن لم توجد
     * ──────────────────────────────────────────────────────────────
     */
    public function getFirstImageAttribute(): ?string
    {
        if ($this->image) {
            return $this->image;
        }
        if ($this->images && count($this->images) > 0) {
            return $this->images[0];
        }

        return null;
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: progressPercent
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    حساب نسبة التقدم في المشروع (المبلغ المجموع ÷ المبلغ المستهدف × 100).
     *    إذا كان المبلغ المستهدف <= 0، تعيد 0.
     *    الحد الأقصى للقيمة هو 100.
     * 
     * 📤 المخرجات:
     *    - float ← النسبة المئوية للتقدم (مقربة لأقرب منزلة عشرية)
     * ──────────────────────────────────────────────────────────────
     */
    public function progressPercent(): float
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->raised_amount / $this->goal_amount) * 100, 1));
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: donations
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة HasMany مع نموذج Donation.
     *    جميع التبرعات المرتبطة بهذا المشروع.
     * 
     * 📤 المخرجات:
     *    - HasMany ← علاقة مع Donation
     * ──────────────────────────────────────────────────────────────
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: booted
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسجيل event عند حفظ المشروع.
     *    يقوم بتوليد slug تلقائياً من العنوان الإنجليزي إن لم يكن
     *    موجوداً، مع الاحتفاظ بطول مناسب وإضافة random fallback.
     * 
     * 📤 المخرجات:
     *    - void
     * ──────────────────────────────────────────────────────────────
     */
    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->getTranslation('title', 'en') ?: 'project-'.Str::random(8));
            }
        });
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 النطاق: scopeActive
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    فلترة المشاريع النشطة فقط (is_active = true)
     *    مع ترتيبها حسب sort_order تصاعدياً.
     * 
     * 📥 المدخلات:
     *    - $query: Builder ← استعلام Eloquent الحالي
     * 
     * 📤 المخرجات:
     *    - Builder ← الاستعلام مع شرط is_active = true و orderBy sort_order
     * ──────────────────────────────────────────────────────────────
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

}
