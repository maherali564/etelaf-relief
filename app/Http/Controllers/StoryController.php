<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\DonationService;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(): View
    {
        return view('stories.index', [
            'stories' => Story::active()->paginate(12),
        ]);
    }

    public function show(string $locale, string $id): View
    {
        $story = Story::active()->findOrFail($id);

        $data = app(DonationService::class)->loadDonationPageData(storyId: $story->id);

        $similar = Story::active()->where('id', '!=', $story->id)->limit(3)->get();

        return view('stories.show', ['story' => $story, 'similar' => $similar, ...$data]);
    }
}
