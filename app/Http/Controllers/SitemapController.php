<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Story;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $projects = Project::active()->get();
        $posts = Post::active()->published()->get();
        $stories = Story::active()->get();

        $content = view('sitemap', compact('projects', 'posts', 'stories'))->render();

        return Response::make($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
