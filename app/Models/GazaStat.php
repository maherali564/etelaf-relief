<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GazaStat extends Model
{
    use \App\Models\Concerns\HasTranslations, HasFactory;

    protected $fillable = [
        'label', 'value', 'prefix', 'icon', 'sort_order', 'is_active',
    ];

    public array $translatable = ['label'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
