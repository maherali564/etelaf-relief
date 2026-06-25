<?php

namespace App\Http\Controllers;

use App\Models\MediaItem;
use App\Models\Project;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(string $locale): View
    {
        $mediaItems = Cache::remember('gallery_items', 3600, function () use ($locale) {
            $items = [];

            foreach (Project::where('is_active', true)->get() as $project) {
                if (!empty($project->images)) {
                    foreach ($project->images as $img) {
                        $items[] = [
                            'media_type' => 'image',
                            'source_type' => 'project',
                            'image' => $img,
                            'thumbnail' => $img,
                            'video_id' => null,
                            'video_platform' => null,
                            'title' => trans_field($project, 'title'),
                            'url' => route('projects.show', ['locale' => $locale, 'slug' => $project->slug]),
                        ];
                    }
                }
                if (!empty($project->video_url)) {
                    $platform = null; $videoId = null;
                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $project->video_url, $m)) {
                        $platform = 'youtube'; $videoId = $m[1];
                    } elseif (preg_match('/vimeo\.com\/(\d+)/', $project->video_url, $m)) {
                        $platform = 'vimeo'; $videoId = $m[1];
                    }
                    $items[] = [
                        'media_type' => 'video',
                        'source_type' => 'project',
                        'image' => null,
                        'thumbnail' => $project->video_thumbnail,
                        'video_id' => $videoId,
                        'video_platform' => $platform,
                        'title' => trans_field($project, 'title'),
                        'url' => route('projects.show', ['locale' => $locale, 'slug' => $project->slug]),
                    ];
                }
                if (!empty($project->videos)) {
                    foreach ($project->videos as $vid) {
                        $items[] = [
                            'media_type' => 'video',
                            'source_type' => 'project',
                            'image' => null,
                            'thumbnail' => null,
                            'video_id' => $vid,
                            'video_platform' => 'local',
                            'title' => trans_field($project, 'title'),
                            'url' => route('projects.show', ['locale' => $locale, 'slug' => $project->slug]),
                        ];
                    }
                }
            }

            foreach (Story::limit(100)->get() as $story) {
                $images = $story->images ?? [];
                foreach ($images as $img) {
                    $items[] = [
                        'media_type' => 'image',
                        'source_type' => 'story',
                        'image' => $img,
                        'thumbnail' => $img,
                        'video_id' => null,
                        'video_platform' => null,
                        'title' => trans_field($story, 'title'),
                        'url' => route('stories.show', ['locale' => $locale, 'id' => $story->id]),
                    ];
                }
                if (!empty($story->video_url)) {
                    $platform = null; $videoId = null;
                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $story->video_url, $m)) {
                        $platform = 'youtube'; $videoId = $m[1];
                    } elseif (preg_match('/vimeo\.com\/(\d+)/', $story->video_url, $m)) {
                        $platform = 'vimeo'; $videoId = $m[1];
                    }
                    $items[] = [
                        'media_type' => 'video',
                        'source_type' => 'story',
                        'image' => null,
                        'thumbnail' => $story->video_thumbnail,
                        'video_id' => $videoId,
                        'video_platform' => $platform,
                        'title' => trans_field($story, 'title'),
                        'url' => route('stories.show', ['locale' => $locale, 'id' => $story->id]),
                    ];
                }
                if (!empty($story->videos)) {
                    foreach ($story->videos as $vid) {
                        $items[] = [
                            'media_type' => 'video',
                            'source_type' => 'story',
                            'image' => null,
                            'thumbnail' => null,
                            'video_id' => $vid,
                            'video_platform' => 'local',
                            'title' => trans_field($story, 'title'),
                            'url' => route('stories.show', ['locale' => $locale, 'id' => $story->id]),
                        ];
                    }
                }
            }

            foreach (MediaItem::active()->get() as $media) {
                if ($media->type === 'video') {
                    $videoId = null;
                    $platform = null;
                    if ($media->video_platform === 'youtube' && $media->video_url) {
                        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $media->video_url, $m);
                        $videoId = $m[1] ?? null;
                        $platform = 'youtube';
                    } elseif ($media->video_platform === 'vimeo' && $media->video_url) {
                        preg_match('/vimeo\.com\/(\d+)/', $media->video_url, $m);
                        $videoId = $m[1] ?? null;
                        $platform = 'vimeo';
                    }
                    $items[] = [
                        'media_type' => 'video',
                        'source_type' => 'media',
                        'image' => null,
                        'thumbnail' => $media->thumbnail,
                        'video_id' => $videoId,
                        'video_platform' => $platform,
                        'title' => trans_field($media, 'title'),
                        'url' => $media->url,
                    ];
                } else {
                    $items[] = [
                        'media_type' => 'image',
                        'source_type' => 'media',
                        'image' => $media->image,
                        'thumbnail' => $media->thumbnail ?? $media->image,
                        'video_id' => null,
                        'video_platform' => null,
                        'title' => trans_field($media, 'title'),
                        'url' => $media->url,
                    ];
                }
            }

            shuffle($items);
            return $items;
        });

        $page = Cache::remember('gallery_page_'.$locale, 3600, function () {
            return \App\Models\Page::where('slug', 'gallery')->first();
        });

        return view('gallery.index', compact('mediaItems', 'page'));
    }
}
