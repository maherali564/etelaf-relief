<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 النموذج: PaymentGateway (بوابة الدفع)
 * ──────────────────────────────────────────────────────────────
 * 🗃️ الجدول: payment_gateways
 * 
 * 🎯 الغرض:
 *    يمثل بوابة دفع متكاملة على المنصة (مثل Stripe, PayPal, Wise).
 *    يحتوي على إعدادات البوابة، والرسوم، والعملات المدعومة،
 *    وحالة التفعيل. يتم إنشاء الـ slug و webhook_url تلقائياً
 *    عند إنشاء بوابة جديدة.
 * 
 * 📥 الحقول القابلة للتعبئة (fillable):
 *    - name, slug, driver ← الاسم ومعرف الرابط والمحرك
 *    - type ← نوع البوابة (card, wallet, bank_transfer, crypto)
 *    - config ← إعدادات البوابة (مشفر)
 *    - logo, sort_order, is_active ← التخصيص والترتيب
 *    - supported_currencies ← العملات المدعومة (مصفوفة)
 *    - min_amount, max_amount ← حدود المبلغ
 *    - processing_fee ← نسبة الرسوم (%)
 *    - webhook_url ← رابط Webhook
 *    - payment_instructions ← تعليمات الدفع (مصفوفة)
 * 
 * 🔄 الـ Casts:
 *    - config → encrypted:array (مشفر)
 *    - is_active → boolean
 *    - supported_currencies → array
 *    - min_amount, max_amount, processing_fee → decimal:2
 *    - payment_instructions → array
 * 
 * 🔗 العلاقات:
 *    - paymentMethods → hasMany PaymentMethod
 * 
 * 🔍 النطاقات (Scopes):
 *    - active ← البوابات النشطة فقط (is_active = true) مرتبة
 *    - byType(type) ← بوابات من نوع معين
 * 
 * ⚙️ Boot Events:
 *    - creating: توليد slug و webhook_url تلقائياً
 * ──────────────────────────────────────────────────────────────
 */
class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'driver', 'type', 'config', 'logo', 'sort_order', 'is_active',
        'supported_currencies', 'min_amount', 'max_amount', 'processing_fee',
        'webhook_url', 'payment_instructions',
    ];

    protected $casts = [
        'config' => 'encrypted:array',
        'is_active' => 'boolean',
        'supported_currencies' => 'array',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'payment_instructions' => 'array',
    ];

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: booted
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسجيل events عند إنشاء بوابة دفع جديدة.
     *    - توليد slug تلقائياً من name أو driver إذا كان فارغاً
     *    - توليد webhook_url تلقائياً بصيغة /webhook/{slug}
     * 
     * 📤 المخرجات:
     *    - void
     * ──────────────────────────────────────────────────────────────
     */
    protected static function booted(): void
    {
        static::creating(function (self $gateway) {
            if (empty($gateway->slug)) {
                $gateway->slug = Str::slug($gateway->name ?: $gateway->driver);
            }
            if (empty($gateway->webhook_url)) {
                $gateway->webhook_url = url('/webhook/'.$gateway->slug);
            }
        });
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: paymentMethods
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة HasMany مع نموذج PaymentMethod.
     *    جميع طرق الدفع المرتبطة بهذه البوابة (مثل: بطاقات،
     *    محافظ إلكترونية، تحويل بنكي).
     * 
     * 📤 المخرجات:
     *    - HasMany ← علاقة مع PaymentMethod
     * ──────────────────────────────────────────────────────────────
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'gateway_id');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 النطاق: scopeActive
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    فلترة البوابات النشطة فقط (is_active = true) مع ترتيبها
     *    حسب حقل sort_order لعرضها بالترتيب المحدد.
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

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 النطاق: scopeByType
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    فلترة البوابات حسب النوع (مثل: card, wallet, bank_transfer, crypto).
     * 
     * 📥 المدخلات:
     *    - $query: Builder ← استعلام Eloquent الحالي
     *    - $type: string ← نوع البوابة المطلوب
     * 
     * 📤 المخرجات:
     *    - Builder ← الاستعلام مع شرط type = $type
     * ──────────────────────────────────────────────────────────────
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الخاصية المحسوبة: getSupportedCurrenciesListAttribute
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرجاع قائمة العملات المدعومة من البوابة.
     *    في حال لم يتم تحديد عملات، يعيد ['USD'] كقيمة افتراضية.
     * 
     * 📤 المخرجات:
     *    - array ← مصفوفة رموز العملات المدعومة
     * ──────────────────────────────────────────────────────────────
     */
    public function getSupportedCurrenciesListAttribute(): array
    {
        return $this->supported_currencies ?? ['USD'];
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: calculateFee
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    حساب رسوم المعالجة (processing fee) بناءً على نسبة مئوية
     *    من المبلغ. إذا كانت النسبة 0 أو أقل، لا توجد رسوم.
     * 
     * 📥 المدخلات:
     *    - $amount: float ← المبلغ الأصلي
     * 
     * 📤 المخرجات:
     *    - float ← قيمة الرسوم (مقربة لمنزلتين عشريتين)
     * ──────────────────────────────────────────────────────────────
     */
    public function calculateFee(float $amount): float
    {
        if ($this->processing_fee > 0) {
            return round($amount * ($this->processing_fee / 100), 2);
        }

        return 0;
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: isAmountValid
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن المبلغ المطلوب ضمن الحدود المسموح بها
     *    للبوابة (بين min_amount و max_amount إن وجدا).
     * 
     * 📥 المدخلات:
     *    - $amount: float ← المبلغ المراد التحقق منه
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كان المبلغ ضمن الحدود، false خلاف ذلك
     * ──────────────────────────────────────────────────────────────
     */
    public function isAmountValid(float $amount): bool
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }
        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }
}
