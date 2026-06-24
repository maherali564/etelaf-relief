<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\SiteSetting;
use App\Models\Statistic;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $stats = Cache::remember('about_stats', 300, function () {
            return [
                'totalDonations' => Donation::completed()->count(),
                'totalRaised' => Donation::completed()->sum('amount'),
                'totalDonors' => Donation::completed()->distinct('email')->count('email'),
            ];
        });
        $totalDonations = $stats['totalDonations'];
        $totalRaised = $stats['totalRaised'];
        $totalDonors = $stats['totalDonors'];
        $achievementStats = Statistic::active()->ofType(Statistic::TYPE_ACHIEVEMENT)->get();

        $settings = SiteSetting::current();
        $aboutFeatures = $settings->about_features;
        if (!is_array($aboutFeatures)) {
            $decoded = is_string($aboutFeatures) ? json_decode($aboutFeatures, true) : null;
            $aboutFeatures = is_array($decoded) ? $decoded : (is_string($aboutFeatures) ? array_filter(explode("\n", str_replace("\r", '', $aboutFeatures))) : []);
        }

        return view('pages.about', compact(
            'totalDonations', 'totalRaised', 'totalDonors', 'achievementStats', 'settings', 'aboutFeatures'
        ));
    }
}
