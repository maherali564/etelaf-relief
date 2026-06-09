<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title', 'content', 'person_name', 'age',
        'location', 'image', 'images', 'video_url', 'goal_amount', 'raised_amount',
        'is_active', 'sort_order',
    ];

    public array $translatable = ['title', 'content'];

    protected $casts = [
        'is_active' => 'boolean',
        'images' => 'array',
        'goal_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
    ];

    public function progressPercent(): float
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->raised_amount / $this->goal_amount) * 100, 1));
    }

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

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
