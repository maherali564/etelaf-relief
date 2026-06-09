<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title', 'subtitle', 'image', 'button_text', 'button_link',
        'sort_order', 'is_active',
    ];

    public array $translatable = ['title', 'subtitle', 'button_text'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
