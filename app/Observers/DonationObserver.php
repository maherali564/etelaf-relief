<?php

namespace App\Observers;

use App\Mail\DonationCertificate;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Project;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DonationObserver
{
    public function creating(Donation $donation): void
    {
        if (empty($donation->transaction_id)) {
            $donation->transaction_id = 'TXN-'.strtoupper(uniqid());
        }
    }

    public function created(Donation $donation): void
    {
        Cache::forget('home.latest_donations');

        if ($donation->status === 'completed') {
            $this->updateRaisedAmount($donation);
            $this->sendCertificateEmail($donation);
        }
    }

    public function updated(Donation $donation): void
    {
        Cache::forget('home.latest_donations');

        if ($donation->wasChanged('status') && $donation->status === 'completed') {
            $this->updateRaisedAmount($donation);
            $this->sendCertificateEmail($donation);
        }
    }

    public function deleted(Donation $donation): void
    {
        Cache::forget('home.latest_donations');
    }

    private function updateRaisedAmount(Donation $donation): void
    {
        if ($donation->campaign_id) {
            Campaign::where('id', $donation->campaign_id)->increment('raised_amount', $donation->amount);
            Cache::forget('home.campaigns');
        }
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
