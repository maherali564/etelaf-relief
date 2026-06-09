<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonationStoreRequest;
use App\Models\Donation;
use App\Models\Post;
use App\Models\Project;
use App\Models\Story;
use App\Services\DonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(
        private readonly DonationService $donationService
    ) {}

    public function projectPage(string $locale, string $slug): View
    {
        $project = Project::where('slug', $slug)->active()->firstOrFail();
        $data = $this->donationService->loadDonationPageData(projectId: $project->id);

        return view('donate.project', ['project' => $project, ...$data]);
    }

    public function postPage(string $locale, string $slug): View
    {
        $post = Post::where('slug', $slug)->active()->published()->firstOrFail();
        $data = $this->donationService->loadDonationPageData(postId: $post->id);

        return view('donate.post', ['post' => $post, ...$data]);
    }

    public function storyPage(string $locale, string $id): View
    {
        $story = Story::active()->findOrFail($id);
        $stories = Story::active()->where('id', '!=', $story->id)->get();
        $data = $this->donationService->loadDonationPageData(storyId: $story->id);

        return view('donate.story', ['story' => $story, 'stories' => $stories, ...$data]);
    }

    public function donorWall(string $locale): View
    {
        $donations = Donation::with(['project', 'story', 'campaign'])->completed()->latest()->paginate(50);

        $wallStats = Cache::remember('donor_wall_stats', 300, function () {
            return [
                'totalRaised' => Donation::completed()->sum('amount'),
                'totalDonors' => Donation::completed()->select('email')->distinct()->count(),
            ];
        });
        $totalRaised = $wallStats['totalRaised'];
        $totalDonors = $wallStats['totalDonors'];

        return view('donor-wall', compact('donations', 'totalRaised', 'totalDonors'));
    }

    public function store(DonationStoreRequest $request): RedirectResponse
    {
        try {
            $donation = $this->donationService->processDonation($request->validated());

            if ($donation->payment_method_id) {
                $result = $this->donationService->initiatePayment($donation);

                if ($result && $result['type'] === 'redirect' && ! empty($result['url'])) {
                    return redirect()->away($result['url']);
                }

                if ($result && $result['type'] === 'instructions') {
                    $token = $donation->idempotency_key;

                    if ($this->isOfflinePayment($donation)) {
                        return redirect()->route('payment.confirm', [
                            'locale' => $donation->locale,
                            'donation' => $donation->id,
                            'token' => $token,
                        ]);
                    }

                    return redirect()->route('payment.instructions', [
                        'locale' => $donation->locale,
                        'donation' => $donation->id,
                        'token' => $token,
                    ]);
                }
            }

            $this->donationService->sendConfirmationEmail($donation);

            return back()->with('success', __('common.donation_success'));
        } catch (\RuntimeException $e) {
            Log::error('Donation processing failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('common.error_occurred'));
        }
    }

    private function isOfflinePayment(Donation $donation): bool
    {
        return $this->donationService->isOfflinePaymentMethod($donation);
    }
}
