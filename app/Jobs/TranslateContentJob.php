<?php

namespace App\Jobs;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TranslateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 120;

    public function __construct(
        protected string $modelClass,
        protected int $modelId,
        protected array $locales,
    ) {}

    public function handle(TranslationService $translator): void
    {
        $model = $this->modelClass::find($this->modelId);
        if (!$model) {
            Log::warning('TranslateContentJob: model not found', [
                'class' => $this->modelClass,
                'id' => $this->modelId,
            ]);
            return;
        }

        $fields = $model->translatable ?? [];
        if (empty($fields)) {
            return;
        }

        $arabicContent = [];
        foreach ($fields as $field) {
            $ar = $model->getTranslation($field, 'ar', false);
            if (!empty($ar) && is_string($ar) && mb_strlen($ar) <= 5000) {
                $arabicContent[$field] = $ar;
            }
        }

        if (empty($arabicContent)) {
            return;
        }

        foreach ($this->locales as $locale) {
            $needsTranslation = [];
            $fieldKeys = [];

            foreach ($arabicContent as $field => $arText) {
                $existing = $model->getTranslation($field, $locale, false);
                if (empty($existing)) {
                    $needsTranslation[] = $arText;
                    $fieldKeys[] = $field;
                }
            }

            if (empty($needsTranslation)) {
                continue;
            }

            try {
                $translated = $translator->translateBatch($needsTranslation, $locale);

                foreach ($fieldKeys as $i => $field) {
                    $model->setTranslation($field, $locale, $translated[$i]);
                }

                $model->save();
            } catch (\Exception $e) {
                Log::error('TranslateContentJob: translation failed', [
                    'class' => $this->modelClass,
                    'id' => $this->modelId,
                    'locale' => $locale,
                    'error' => $e->getMessage(),
                ]);

                if ($this->attempts() < 3) {
                    $this->release(30 * $this->attempts());
                }
            }
        }
    }
}
