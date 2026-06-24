<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'txid', 'from_address', 'to_address',
        'amount', 'currency', 'status', 'raw_data',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'raw_data' => 'json',
    ];

    public function network(): BelongsTo
    {
        return $this->belongsTo(CryptoNetwork::class, 'crypto_network_id');
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class, 'matched_donation_id');
    }
}
