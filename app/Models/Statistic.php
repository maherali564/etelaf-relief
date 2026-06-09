<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use HasTranslations;

    public const TYPE_ACHIEVEMENT = 'achievement';

    public const TYPE_HUMANITARIAN = 'humanitarian';

    protected $fillable = [
        'type',
        'value',
        'prefix',
        'label',
        'sort_order',
        'is_active',
    ];

    public array $translatable = ['label'];

    protected $casts = [
        'value' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function formattedValue(): string
    {
        $formatted = number_format($this->value);

        return ($this->prefix ?? '').$formatted;
    }
}
