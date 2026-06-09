<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'slug', 'title', 'description', 'content',
        'image', 'images', 'video_url', 'goal_amount', 'raised_amount',
        'is_featured', 'sort_order', 'is_active',
    ];

    public array $translatable = ['title', 'description', 'content'];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'goal_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
    ];

    public function getFirstImageAttribute(): ?string
    {
        if ($this->image) {
            return $this->image;
        }
        if ($this->images && count($this->images) > 0) {
            return $this->images[0];
        }

        return null;
    }

    public function progressPercent(): float
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->raised_amount / $this->goal_amount) * 100, 1));
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->getTranslation('title', 'en') ?: 'project-'.uniqid());
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
