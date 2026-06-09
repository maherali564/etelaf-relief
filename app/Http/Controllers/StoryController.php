<?php

namespace App\Http\Controllers;

use App\Models\Story;
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

        return view('stories.show', compact('story'));
    }
}
