<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TransparencyController extends Controller
{
    public function index(): View
    {
        $stats = Cache::remember('transparency_stats', 300, function () {
            return [
                'totalRaised' => Donation::completed()->sum('amount'),
                'totalDonations' => Donation::completed()->count(),
                'totalDonors' => Donation::completed()->distinct('email')->count('email'),
            ];
        });
        $totalRaised = $stats['totalRaised'];
        $totalDonations = $stats['totalDonations'];
        $totalDonors = $stats['totalDonors'];

        $projectBreakdown = Project::active()->get()->map(fn ($p) => [
            'title' => $p->title,
            'raised' => $p->raised_amount,
            'goal' => $p->goal_amount,
            'percent' => $p->progressPercent(),
        ]);

        $adminCostRate = 5; // 5% administrative costs

        return view('pages.transparency', compact(
            'totalRaised', 'totalDonations', 'totalDonors',
            'projectBreakdown', 'adminCostRate'
        ));
    }
}
