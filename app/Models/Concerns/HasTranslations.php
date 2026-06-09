<?php

namespace App\Models\Concerns;

use Spatie\Translatable\HasTranslations as SpatieHasTranslations;

trait HasTranslations
{
    use SpatieHasTranslations;

    public static function supportedLocales(): array
    {
        return config('app.supported_locales', ['ar', 'en', 'es']);
    }
}
