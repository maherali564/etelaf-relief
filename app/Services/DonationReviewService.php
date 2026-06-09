<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;

class DonationReviewService
{
    /**
     * Approve a donation and mark it as completed
     *
     * @param  Donation  $donation  The donation to approve
     * @param  int  $adminId  The ID of the approving admin
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
     * Reject a donation and mark it as failed
     *
     * @param  Donation  $donation  The donation to reject
     * @param  string|null  $reason  Optional rejection reason
     * @param  int  $adminId  The ID of the rejecting admin
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
