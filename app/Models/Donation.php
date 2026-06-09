<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id', 'donor_name', 'email', 'phone', 'amount', 'currency',
        'payment_method_id', 'transaction_id', 'status',
        'is_anonymous', 'is_recurring', 'recurring_interval',
        'campaign_id', 'project_id', 'post_id', 'story_id',
        'cryptocurrency_id', 'crypto_network_id', 'donated_at',
        'notes', 'locale', 'confirmation_details',
        'reviewed_by', 'reviewed_at', 'rejection_reason',
        'stripe_subscription_id',
        'billing_agreement_id',
        'idempotency_key',
        'payment_attempts',
        'last_error',
        'last_attempt_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'is_recurring' => 'boolean',
        'donated_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'confirmation_details' => 'array',
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function gateway()
    {
        return $this->hasOneThrough(PaymentGateway::class, PaymentMethod::class, 'id', 'id', 'payment_method_id', 'gateway_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function cryptocurrency()
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function cryptoNetwork()
    {
        return $this->belongsTo(CryptoNetwork::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function confirmations()
    {
        return $this->hasMany(PaymentConfirmation::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeByGateway($query, $driver)
    {
        return $query->whereHas('paymentMethod.gateway', fn ($q) => $q->where('driver', $driver));
    }

    public function markCompleted(?int $reviewerId = null): void
    {
        $this->update([
            'status' => 'completed',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }

    public function markFailed(?string $reason = null, ?int $reviewerId = null): void
    {
        $this->update([
            'status' => 'failed',
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }
}
