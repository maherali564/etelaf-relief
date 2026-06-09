<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'volunteer_opportunity_id', 'volunteer_id', 'title',
        'description', 'status', 'hours_logged',
        'started_at', 'completed_at', 'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'hours_logged' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'volunteer_opportunity_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
