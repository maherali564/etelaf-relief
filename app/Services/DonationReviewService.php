<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 DonationReviewService
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    خدمة مراجعة التبرعات التي تتطلب تأكيداً يدوياً
 *    (مثل التحويلات البنكية). توفر عمليات قبول التبرع
 *    (تحويله إلى completed) أو رفضه (تحويله إلى failed)
 *    مع تسجيل النشاط في سجل الحركات (Activity Log) وسجلات
 *    النظام (Log) للأغراض الرقابية والتدقيق.
 *
 * 🔗 الاعتماديات:
 *    - Donation (Model) ← التبرع المراد مراجعته
 *    - activity() (Helper) ← تسجيل النشاط عبر spatie/laravel-activitylog
 *    - Log (Facade) ← تسجيل عمليات القبول والرفض
 * ──────────────────────────────────────────────────────────────
 */
class DonationReviewService
{
    /**
     * ──────────────────────────────────────────────────────────
     * 📌 approve
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    الموافقة على تبرع كان قيد المراجعة (under_review)
     *    وتحويل حالته إلى completed. تسجل العملية في سجل
     *    النشاط ونظام السجلات مع تفاصيل المشرف الموافق
     *    والحالة القديمة والجديدة.
     *
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع المراد الموافقة عليه
     *    - $adminId: int ← معرف المشرف الذي قام بالموافقة
     *
     * 🔗 الاعتماديات:
     *    - Donation::markCompleted() ← تحديث حالة التبرع
     *    - activity() ← تسجيل النشاط عبر spatie package
     *    - Log::info() ← تسجيل في سجل النظام
     * ──────────────────────────────────────────────────────────
     */
    public function approve(Donation $donation, int $adminId): void
    {
        $oldStatus = $donation->status;

        $donation->markCompleted($adminId);

        activity()
            ->performedOn($donation)
            ->causedBy(auth()->user())
            ->withProperties(['old_status' => $oldStatus, 'new_status' => 'completed', 'admin_id' => $adminId])
            ->log('تم تأكيد التبرع');

        Log::info('Donation approved by admin', [
            'donation_id' => $donation->id,
            'admin_id' => $adminId,
        ]);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 reject
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    رفض تبرع كان قيد المراجعة وتحويل حالته إلى failed
     *    مع إمكانية إضافة سبب الرفض. تسجل العملية في سجل
     *    النشاط ونظام السجلات مع تفاصيل المشرف الرافض
     *    والسبب.
     *
     * 📥 المدخلات:
     *    - $donation: Donation ← كائن التبرع المراد رفضه
     *    - $reason: string|null ← سبب الرفض (اختياري)
     *    - $adminId: int ← معرف المشرف الذي قام بالرفض
     *
     * 🔗 الاعتماديات:
     *    - Donation::markFailed() ← تحديث حالة التبرع إلى فاشل
     *    - activity() ← تسجيل النشاط
     *    - Log::info() ← تسجيل في سجل النظام
     * ──────────────────────────────────────────────────────────
     */
    public function reject(Donation $donation, ?string $reason, int $adminId): void
    {
        $oldStatus = $donation->status;

        $donation->markFailed($reason, $adminId);

        activity()
            ->performedOn($donation)
            ->causedBy(auth()->user())
            ->withProperties(['old_status' => $oldStatus, 'reason' => $reason, 'admin_id' => $adminId])
            ->log('تم رفض التبرع');

        Log::info('Donation rejected by admin', [
            'donation_id' => $donation->id,
            'admin_id' => $adminId,
            'reason' => $reason,
        ]);
    }
}
