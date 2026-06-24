<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cryptocurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_translated', 'symbol', 'logo', 'min_amount', 'max_amount',
        'processing_fee', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'min_amount' => 'decimal:8',
        'max_amount' => 'decimal:8',
        'processing_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'name_translated' => 'array',
    ];

    public function networks()
    {
        return $this->hasMany(CryptoNetwork::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        if (! empty($this->name_translated[$locale])) {
            return $this->name_translated[$locale];
        }

        return $this->name;
    }
}
