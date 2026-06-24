<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $locale, string $slug): View
    {
        $page = Page::findBySlug($slug) ?? abort(404);

        return view('pages.show', compact('page'));
    }
}
