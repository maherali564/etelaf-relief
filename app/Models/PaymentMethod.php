<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_translated', 'description', 'account_info', 'instructions', 'icon',
        'gateway_id', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'name_translated' => 'array',
    ];

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
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
