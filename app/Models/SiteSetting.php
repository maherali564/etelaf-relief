<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasTranslations;

    protected $casts = [
        'logos' => 'array',
        'enabled_locales' => 'array',
        'show_announcements' => 'boolean',
        'show_success_stories' => 'boolean',
        'show_donor_wall' => 'boolean',
    ];

    protected $fillable = [
        'site_name',
        'tagline',
        'hero_title',
        'hero_subtitle',
        'about_title',
        'about_content',
        'about_features',
        'donate_title',
        'donate_description',
        'donate_methods',
        'footer_description',
        'phone',
        'email',
        'whatsapp',
        'twitter',
        'facebook',
        'logo',
        'logos',
        'enabled_locales',
        'hero_image',
        'about_image',
        'show_announcements',
        'show_success_stories',
        'show_donor_wall',
    ];

    public array $translatable = [
        'site_name',
        'tagline',
        'hero_title',
        'hero_subtitle',
        'about_title',
        'about_content',
        'about_features',
        'donate_title',
        'donate_description',
        'donate_methods',
        'footer_description',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
