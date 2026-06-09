<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasTranslations;

    public const TYPE_ANNOUNCEMENT = 'announcement';

    public const TYPE_SUCCESS_STORY = 'success_story';

    public const TYPE_NEWS = 'news';

    protected $fillable = [
        'type', 'slug', 'title', 'excerpt', 'content',
        'image', 'images', 'campaign_id',
        'published_at', 'is_active',
    ];

    public array $translatable = ['title', 'excerpt', 'content'];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active' => 'boolean',
        'images' => 'array',
    ];

    public function getFirstImageAttribute(): ?string
    {
        if ($this->images && count($this->images) > 0) {
            return $this->images[0];
        }

        return $this->image;
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->getTranslation('title', 'en') ?: 'post-'.uniqid());
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now())->orderByDesc('published_at');
    }
}
