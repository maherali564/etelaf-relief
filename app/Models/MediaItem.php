<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'type',
        'image',
        'video_url',
        'video_platform',
        'thumbnail',
        'title',
        'description',
        'url',
        'sort_order',
        'is_active',
    ];

    public array $translatable = ['title', 'description'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
