<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class Donor extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'is_active', 'preferred_locale',
        'last_login_at', 'total_donated', 'donation_count', 'notes',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function getComputedTotalDonatedAttribute(): float
    {
        return (float) Cache::remember(
            "donor.{$this->id}.total_donated",
            300,
            fn () => $this->donations()->completed()->sum('amount')
        );
    }

    public function getComputedDonationCountAttribute(): int
    {
        return Cache::remember(
            "donor.{$this->id}.donation_count",
            300,
            fn () => $this->donations()->completed()->count()
        );
    }

    protected static function booted(): void
    {
        static::saved(function (Donor $donor) {
            Cache::forget("donor.{$donor->id}.total_donated");
            Cache::forget("donor.{$donor->id}.donation_count");
        });
    }

    public function updateComputedStats(): void
    {
        Cache::forget("donor.{$this->id}.total_donated");
        Cache::forget("donor.{$this->id}.donation_count");
    }
}
