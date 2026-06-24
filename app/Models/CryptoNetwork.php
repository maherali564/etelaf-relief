<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'network_name', 'wallet_address', 'qr_code',
        'contract_address', 'min_amount', 'processing_fee', 'explorer_url',
        'notes', 'last_checked_at', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_amount' => 'decimal:8',
        'processing_fee' => 'decimal:2',
        'last_checked_at' => 'datetime',
    ];

    public function cryptocurrency()
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getExplorerLinkAttribute(): ?string
    {
        if ($this->explorer_url && $this->wallet_address) {
            return str_replace('{address}', $this->wallet_address, $this->explorer_url);
        }

        return null;
    }

    public function getExplorerTxLinkAttribute(): ?string
    {
        return $this->explorer_url
            ? str_replace('{txid}', '{address}', $this->explorer_url)
            : null;
    }
}
