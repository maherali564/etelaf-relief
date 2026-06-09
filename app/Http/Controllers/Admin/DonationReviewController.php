<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DonationRejectRequest;
use App\Models\Donation;
use App\Services\DonationReviewService;
use Illuminate\Http\Request;

class DonationReviewController extends Controller
{
    public function __construct(
        private readonly DonationReviewService $reviewService
    ) {}

    public function approve(Request $request, Donation $donation)
    {
        $this->authorize('update', $donation);

        if (! in_array($donation->status, ['under_review', 'pending'])) {
            return back()->with('error', 'هذا التبرع ليس في حالة مراجعة');
        }

        $this->reviewService->approve($donation, auth()->id());

        return back()->with('success', 'تم تأكيد التبرع بنجاح');
    }

    public function reject(DonationRejectRequest $request, Donation $donation)
    {
        $this->authorize('update', $donation);

        if (! in_array($donation->status, ['under_review', 'pending'])) {
            return back()->with('error', 'هذا التبرع ليس في حالة مراجعة');
        }

        $this->reviewService->reject($donation, $request->reason ?? null, auth()->id());

        return back()->with('success', 'تم رفض التبرع');
    }
}
