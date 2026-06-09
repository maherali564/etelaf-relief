<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'driver', 'type', 'config', 'logo', 'sort_order', 'is_active',
        'supported_currencies', 'min_amount', 'max_amount', 'processing_fee',
        'webhook_url', 'payment_instructions',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'supported_currencies' => 'array',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'payment_instructions' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $gateway) {
            if (empty($gateway->slug)) {
                $gateway->slug = Str::slug($gateway->name ?: $gateway->driver);
            }
            if (empty($gateway->webhook_url)) {
                $gateway->webhook_url = url('/webhook/'.$gateway->slug);
            }
        });
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'gateway_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getSupportedCurrenciesListAttribute(): array
    {
        return $this->supported_currencies ?? ['USD'];
    }

    public function calculateFee(float $amount): float
    {
        if ($this->processing_fee > 0) {
            return round($amount * ($this->processing_fee / 100), 2);
        }

        return 0;
    }

    public function isAmountValid(float $amount): bool
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }
        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }
}
