<?php

namespace App\Observers;

use App\Jobs\TranslateContentJob;
use Illuminate\Database\Eloquent\Model;

class TranslationObserver
{
    public function saved(Model $model): void
    {
        $fields = $model->translatable ?? [];
        if (empty($fields)) {
            return;
        }

        $locales = config('app.supported_locales', ['ar', 'en', 'es', 'id', 'tr']);

        $targetLocales = array_values(array_filter($locales, fn($l) => $l !== 'ar'));
        if (empty($targetLocales)) {
            return;
        }

        $hasArabic = false;
        foreach ($fields as $field) {
            $ar = $model->getTranslation($field, 'ar', false);
            if (!empty($ar) && is_string($ar)) {
                $hasArabic = true;
                break;
            }
        }

        if (!$hasArabic) {
            return;
        }

        dispatch(new TranslateContentJob(
            get_class($model),
            $model->id,
            $targetLocales,
        ));
    }
}
