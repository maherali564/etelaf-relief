<?php

namespace App\Services;

use App\Mail\DonationConfirmation;
use App\Models\Campaign;
use App\Models\Cryptocurrency;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\Story;
use App\Services\Payment\IdempotencyHelper;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class DonationService
{
    /**
     * Load data needed for donation page with caching
     */
    public function loadDonationPageData(?int $projectId = null, ?int $postId = null, ?int $storyId = null): array
    {
        $cacheKey = 'donation_page_data_'.md5(serialize(compact('projectId', 'postId', 'storyId')));

        return Cache::remember($cacheKey, 300, function () use ($projectId, $postId, $storyId) {
            return [
                'paymentMethods' => PaymentMethod::with('gateway')->active()->get(),
                'campaigns' => Campaign::active()->get(),
                'projects' => Project::active()->get(),
                'stories' => Story::active()->get(),
                'cryptocurrencies' => Cryptocurrency::with('networks')->active()->get(),
                'donations' => Donation::completed()
                    ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
                    ->when($postId, fn ($q) => $q->where('post_id', $postId))
                    ->when($storyId, fn ($q) => $q->where('story_id', $storyId))
                    ->latest()
                    ->limit(20)
                    ->get(),
            ];
        });
    }

    /**
     * Process and store a new donation
     *
     * @param  array  $validated  Validated donation data
     * @return Donation The created donation instance
     */
    public function processDonation(array $validated): Donation
    {
        return DB::transaction(function () use ($validated) {
            $donor = Donor::where('email', $validated['email'])->first();

            $driver = PaymentMethod::find($validated['payment_method_id'])?->gateway?->driver ?? 'unknown';

            $donation = Donation::create([
                ...$validated,
                'donor_id' => $donor?->id,
                'currency' => 'USD',
                'status' => 'pending',
                'locale' => app()->getLocale(),
                'donated_at' => now(),
                'idempotency_key' => IdempotencyHelper::generateKey($driver),
            ]);

            return $donation;
        });
    }

    /**
     * Initiate payment for a donation with retry logic
     *
     * @param  Donation  $donation  The donation to process payment for
     * @return array|null Payment result data (redirect URL or instructions)
     *
     * @throws RuntimeException After max retry attempts
     */
    public function initiatePayment(Donation $donation): ?array
    {
        if (! $donation->payment_method_id) {
            return null;
        }

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $payment = PaymentService::fromDonation($donation);
                $result = $payment->initPayment($donation);

                if ($attempt > 1) {
                    Log::info('Payment retry succeeded', [
                        'donation_id' => $donation->id,
                        'attempt' => $attempt,
                    ]);
                }

                return $result;
            } catch (RuntimeException $e) {
                Log::error('Payment initiation failed', [
                    'donation_id' => $donation->id,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                $donation->update([
                    'payment_attempts' => $attempt,
                    'last_error' => $e->getMessage(),
                    'last_attempt_at' => now(),
                ]);

                if ($attempt < $maxAttempts) {
                    usleep($attempt * 500000);
                }
            }
        }

        throw new RuntimeException('فشلت محاولات الدفع بعد '.$maxAttempts.' محاولات');
    }

    /**
     * Check if the donation uses an offline payment method
     */
    public function isOfflinePaymentMethod(Donation $donation): bool
    {
        $driver = $donation->paymentMethod?->gateway?->driver ?? '';

        return in_array($driver, ['bank_transfer', 'wise', 'crypto']);
    }

    /**
     * Send donation confirmation email to donor
     */
    public function sendConfirmationEmail(Donation $donation): void
    {
        try {
            Mail::to($donation->email)->send(new DonationConfirmation($donation, 'under_review'));
        } catch (\Exception $e) {
            Log::error('Donation confirmation email failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
