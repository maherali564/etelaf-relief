<?php

namespace App\Observers;

use App\Mail\DonationCertificate;
use App\Models\Donation;
use App\Models\Project;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * مراقب أحداث التبرعات (Donation Observer)
 *
 * يستمع للأحداث المتعلقة بنموذج التبرع (إنشاء، تحديث، حذف) ويقوم بالإجراءات اللازمة مثل:
 * - إنشاء معرف معاملة فريد (transaction_id) تلقائياً قبل الإنشاء
 * - مسح الكاش الخاص بالصفحة الرئيسية عند أي تغيير في التبرعات
 * - تحديث المبلغ المُجمّع للحملات والمشاريع والقصص المرتبطة بالتبرع
 * - إرسال شهادة التبرع عبر البريد الإلكتروني عند إتمام التبرع
 * - مسح الكاش الخاص بالمتبرع (المبلغ الإجمالي وعدد التبرعات)
 */
class DonationObserver
{
    /**
     * يتم تنفيذه قبل إنشاء سجل التبرع.
     * يقوم بإنشاء معرف معاملة فريد (transaction_id) إذا لم يكن موجوداً.
     *
     * @param  Donation  $donation  كائن التبرع الجاري إنشاؤه
     */
    public function creating(Donation $donation): void
    {
        if (empty($donation->transaction_id)) {
            $donation->transaction_id = 'TXN-'.strtoupper(Str::random(16));
        }
    }

    /**
     * يتم تنفيذه بعد إنشاء سجل التبرع بنجاح.
     * يقوم بمسح كاش آخر التبرعات في الصفحة الرئيسية،
     * وإذا كانت حالة التبرع "مكتمل" يقوم بتحديث المبلغ المُجمّع وإرسال شهادة التبرع.
     *
     * @param  Donation  $donation  كائن التبرع الذي تم إنشاؤه
     */
    public function created(Donation $donation): void
    {
        Cache::forget('home.latest_donations');

        if ($donation->status === 'completed') {
            $this->updateRaisedAmount($donation);
            $this->sendCertificateEmail($donation);
        }
    }

    /**
     * يتم تنفيذه بعد تحديث سجل التبرع.
     * يقوم بمسح كاش آخر التبرعات،
     * وإذا تغيرت حالة التبرع إلى "مكتمل" يقوم بتحديث المبلغ المُجمّع وإرسال الشهادة.
     *
     * @param  Donation  $donation  كائن التبرع الذي تم تحديثه
     */
    public function updated(Donation $donation): void
    {
        Cache::forget('home.latest_donations');

        if ($donation->wasChanged('status') && $donation->status === 'completed') {
            $this->updateRaisedAmount($donation);
            $this->sendCertificateEmail($donation);
        }
    }

    /**
     * يتم تنفيذه بعد حذف سجل التبرع.
     * يقوم بمسح كاش آخر التبرعات في الصفحة الرئيسية.
     *
     * @param  Donation  $donation  كائن التبرع الذي تم حذفه
     */
    public function deleted(Donation $donation): void
    {
        Cache::forget('home.latest_donations');
    }

    /**
     * تحديث المبلغ المُجمّع (raised_amount) للكيانات المرتبطة بالتبرع.
     *
     * يزيد المبلغ المُجمّع للحملة أو المشروع أو القصة المرتبطة بالتبرع،
     * ويمسح الكاش الخاص بالصفحة الرئيسية والمتبرع عند الحاجة.
     *
     * @param  Donation  $donation  كائن التبرع المكتمل
     */
    private function updateRaisedAmount(Donation $donation): void
    {
        if ($donation->project_id) {
            Project::where('id', $donation->project_id)->increment('raised_amount', $donation->amount);
            Cache::forget('home.projects');
        }
        if ($donation->story_id) {
            Story::where('id', $donation->story_id)->increment('raised_amount', $donation->amount);
        }

        if ($donation->donor_id) {
            Cache::forget("donor.{$donation->donor_id}.total_donated");
            Cache::forget("donor.{$donation->donor_id}.donation_count");
        }
    }

    /**
     * إرسال شهادة التبرع عبر البريد الإلكتروني.
     *
     * ترسل شهادة (DonationCertificate) إلى البريد الإلكتروني المسجل في التبرع.
     * إذا لم يكن هناك بريد إلكتروني، تتجاوز الإرسال.
     * يتم تسجيل أي خطأ في السجل دون إيقاف العملية.
     *
     * @param  Donation  $donation  كائن التبرع المكتمل
     */
    private function sendCertificateEmail(Donation $donation): void
    {
        if (! $donation->email) {
            return;
        }
        try {
            Mail::to($donation->email)->send(new DonationCertificate($donation));
        } catch (\Exception $e) {
            Log::error('Certificate email failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
