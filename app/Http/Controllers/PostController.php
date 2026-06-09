<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('posts.index', [
            'posts' => Post::active()->published()->paginate(12),
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $post = Post::query()->where('slug', $slug)->active()->published()->firstOrFail();

        return view('posts.show', compact('post'));
    }

    public function announcements(): View
    {
        return view('posts.index', [
            'posts' => Post::active()->ofType(Post::TYPE_ANNOUNCEMENT)->published()->paginate(12),
            'title' => __('common.nav_announcements'),
        ]);
    }

    public function successStories(): View
    {
        return view('posts.index', [
            'posts' => Post::active()->ofType(Post::TYPE_SUCCESS_STORY)->published()->paginate(12),
            'title' => __('common.nav_success_stories'),
        ]);
    }
}
