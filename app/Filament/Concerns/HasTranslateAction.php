<?php

namespace App\Filament\Concerns;

use App\Services\TranslationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

trait HasTranslateAction
{
    public function translateAll(): void
    {
        $translator = app(TranslationService::class);
        $model = new ($this->getModel());
        $fields = $model->translatable ?? [];
        $locales = config('app.supported_locales', ['ar', 'en', 'es', 'id', 'tr']);
        $targetLocales = array_values(array_filter($locales, fn($l) => $l !== 'ar'));
        $updated = false;

        foreach ($fields as $field) {
            $arContent = $this->data[$field]['ar'] ?? '';
            if (empty($arContent)) {
                continue;
            }

            foreach ($targetLocales as $locale) {
                try {
                    $translated = $translator->translate($arContent, $locale);
                    $this->data[$field][$locale] = $translated;
                    $updated = true;
                } catch (\Exception $e) {
                    logger()->error('AutoTranslate failed', [
                        'field' => $field,
                        'locale' => $locale,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($updated) {
            Notification::make()
                ->title(__('admin.translate_success'))
                ->success()
                ->send();
        }
    }

    protected function getTranslateAction(): Action
    {
        return Action::make('autoTranslate')
            ->label(__('admin.auto_translate'))
            ->icon('heroicon-o-language')
            ->action('translateAll');
    }
}
