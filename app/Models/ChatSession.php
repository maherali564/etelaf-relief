<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_name',
        'visitor_email',
        'visitor_ip',
        'visitor_url',
        'status',
        'assigned_to',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function assignTo(User $user): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'ended_at' => now(),
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'warning',
            'active' => 'success',
            'closed' => 'secondary',
            default => 'gray',
        };
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->started_at) {
            return null;
        }
        $end = $this->ended_at ?? now();

        return $this->started_at->diffForHumans($end, ['parts' => 2, 'short' => true]);
    }

    public function hasUnreadMessages(): bool
    {
        return $this->messages()->where('is_from_admin', false)->where('is_read', false)->exists();
    }
}
