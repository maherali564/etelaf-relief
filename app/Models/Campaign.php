<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'title', 'description', 'goal_amount', 'raised_amount',
        'image', 'images', 'slug', 'start_date', 'end_date', 'is_active',
    ];

    public array $translatable = ['title', 'description'];

    protected $casts = [
        'goal_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'images' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function progressPercent(): float
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->raised_amount / $this->goal_amount) * 100, 1));
    }
}
