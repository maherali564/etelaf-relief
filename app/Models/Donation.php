<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 النموذج: Donation (التبرع)
 * ──────────────────────────────────────────────────────────────
 * 🗃️ الجدول: donations
 * 
 * 🎯 الغرض:
 *    يمثل عملية تبرع فردية على المنصة. يتتبّع حالة التبرع
 *    (معلق، قيد المراجعة، مكتمل، فاشل)، والمبلغ، والعملة،
 *    وبيانات المتبرع، وطريقة الدفع المستخدمة، والبوابة المرتبطة.
 *    يمكن ربط التبرع بحملة أو مشروع أو منشور أو قصة.
 * 
 * 📥 الحقول القابلة للتعبئة (fillable):
 *    - donor_name, email, phone ← بيانات المتبرع الأساسية
 *    - amount, currency ← المبلغ والعملة
 *    - status ← الحالة (pending, under_review, completed, failed)
 *    - is_anonymous ← تبرع مجهول؟
 *    - is_recurring, recurring_interval ← تبرع متكرر؟
 *    - donated_at, notes, locale ← بيانات إضافية
 *    - confirmation_details ← تفاصيل التأكيد من البوابة
 *    - reviewed_by, reviewed_at, rejection_reason ← المراجعة الإدارية
 *    - idempotency_key, payment_attempts, last_error, last_attempt_at ← منع التكرار
 * 
 * 🔄 الـ Casts:
 *    - amount → decimal:2
 *    - is_anonymous, is_recurring → boolean
 *    - donated_at, reviewed_at → datetime (Carbon)
 *    - confirmation_details → array (JSON)
 * 
 * 🔗 العلاقات:
 *    - donor → belongsTo Donor
 *    - paymentMethod → belongsTo PaymentMethod
 *    - gateway → hasOneThrough PaymentGateway (عبر PaymentMethod)
     *    - project → belongsTo Project
     *    - story → belongsTo Story
 *    - cryptocurrency → belongsTo Cryptocurrency
 *    - cryptoNetwork → belongsTo CryptoNetwork
 *    - reviewer → belongsTo User (للمراجعة الإدارية)
 *    - confirmations → hasMany PaymentConfirmation
 * 
 * 🔍 النطاقات (Scopes):
 *    - completed ← الحالة = completed
 *    - pending ← الحالة = pending
 *    - underReview ← الحالة = under_review
 *    - latest ← ترتيب تنازلي حسب created_at
 *    - byGateway(driver) ← عبر بوابة دفع محددة
 * ──────────────────────────────────────────────────────────────
 */
class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_name', 'email', 'phone', 'amount', 'currency', 'status',
        'is_anonymous', 'is_recurring', 'recurring_interval', 'donated_at',
        'notes', 'locale', 'confirmation_details',
        'reviewed_by', 'reviewed_at', 'rejection_reason',
        'idempotency_key', 'payment_attempts', 'last_error', 'last_attempt_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'is_recurring' => 'boolean',
        'donated_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'confirmation_details' => 'array',
    ];

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: donor
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج Donor.
     *    المتبرع المسجل الذي قام بعملية التبرع (قد يكون null في حال
     *    التبرع كزائر دون تسجيل).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع Donor
     * ──────────────────────────────────────────────────────────────
     */
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: paymentMethod
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج PaymentMethod.
     *    طريقة الدفع المستخدمة لإتمام التبرع (بطاقة، محفظة، إلخ).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع PaymentMethod
     * ──────────────────────────────────────────────────────────────
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: gateway
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة HasOneThrough مع نموذج PaymentGateway عبر PaymentMethod.
     *    تسمح بالوصول المباشر إلى بوابة الدفع (Stripe, PayPal, Wise)
     *    التي تم استخدامها لمعالجة هذا التبرع دون الحاجة لتمرير الوسيط.
     * 
     *    المسار: Donation → payment_method_id → PaymentMethod → gateway_id → PaymentGateway
     * 
     * 📤 المخرجات:
     *    - HasOneThrough ← علاقة مع PaymentGateway
     * ──────────────────────────────────────────────────────────────
     */
    public function gateway()
    {
        return $this->hasOneThrough(PaymentGateway::class, PaymentMethod::class, 'id', 'id', 'payment_method_id', 'gateway_id');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: campaign
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج Campaign.
     *    الحملة التي خُصص لها هذا التبرع (إن وجد).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع Campaign
     * ──────────────────────────────────────────────────────────────
     */
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: project
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج Project.
     *    المشروع الذي خُصص له هذا التبرع (إن وجد).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع Project
     * ──────────────────────────────────────────────────────────────
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: post
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج Post.
     *    المنشور الذي أتى منه المتبرع (إن وجد).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع Post
     * ──────────────────────────────────────────────────────────────
     */
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: story
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج Story.
     *    القصة التي أتى منها المتبرع (إن وجد).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع Story
     * ──────────────────────────────────────────────────────────────
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: cryptocurrency
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج Cryptocurrency.
     *    العملة الرقمية المستخدمة في التبرع (إن كان قد تم عبر
     *    العملات الرقمية).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع Cryptocurrency
     * ──────────────────────────────────────────────────────────────
     */
    public function cryptocurrency()
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: cryptoNetwork
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج CryptoNetwork.
     *    شبكة العملة الرقمية المستخدمة في التبرع (مثل ERC-20, BEP-20).
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع CryptoNetwork
     * ──────────────────────────────────────────────────────────────
     */
    public function cryptoNetwork()
    {
        return $this->belongsTo(CryptoNetwork::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: reviewer
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة BelongsTo مع نموذج User عبر المفتاح الأجنبي 'reviewed_by'.
     *    المستخدم الإداري الذي راجع هذا التبرع ووافق عليه أو رفضه.
     * 
     * 📤 المخرجات:
     *    - BelongsTo ← علاقة مع User
     * ──────────────────────────────────────────────────────────────
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: confirmations
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    علاقة HasMany مع نموذج PaymentConfirmation.
     *    سجلات تأكيد الدفع المرتبطة بهذا التبرع (قد تكون متعددة
     *    في حال إعادة المحاولة أو التأكيدات الجزئية).
     * 
     * 📤 المخرجات:
     *    - HasMany ← علاقة مع PaymentConfirmation
     * ──────────────────────────────────────────────────────────────
     */
    public function confirmations()
    {
        return $this->hasMany(PaymentConfirmation::class);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 النطاق: scopeCompleted — ملاحظة: scopePending, scopeUnderReview, scopeLatest, scopeByGateway أزيلت لأنها غير مستخدمة
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    فلترة التبرعات للحصول على التبرعات المكتملة فقط
     *    (حيث status = 'completed').
     * 
     * 📥 المدخلات:
     *    - $query: Builder ← استعلام Eloquent الحالي
     * 
     * 📤 المخرجات:
     *    - Builder ← الاستعلام مع شرط الحالة = completed
     * ──────────────────────────────────────────────────────────────
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: markCompleted
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تحديث حالة التبرع إلى 'مكتمل' (completed) وتسجيل المراجع
     *    وتاريخ المراجعة.
     * 
     * 📥 المدخلات:
     *    - $reviewerId: int|null ← معرف المستخدم الذي راجع التبرع (اختياري)
     * 
     * 📤 المخرجات:
     *    - void
     * ──────────────────────────────────────────────────────────────
     */
    public function markCompleted(?int $reviewerId = null): void
    {
        $this->update([
            'status' => 'completed',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: markFailed
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تحديث حالة التبرع إلى 'فاشل' (failed) مع تسجيل سبب الرفض
     *    والمراجع وتاريخ المراجعة.
     * 
     * 📥 المدخلات:
     *    - $reason: string|null ← سبب الرفض أو الفشل (اختياري)
     *    - $reviewerId: int|null ← معرف المستخدم الذي راجع التبرع (اختياري)
     * 
     * 📤 المخرجات:
     *    - void
     * ──────────────────────────────────────────────────────────────
     */
    public function markFailed(?string $reason = null, ?int $reviewerId = null): void
    {
        $this->update([
            'status' => 'failed',
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }
}
