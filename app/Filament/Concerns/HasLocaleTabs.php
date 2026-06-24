<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

trait HasLocaleTabs
{
    protected static function localeTabs(string $field, string $label, string $type = 'text'): Tabs
    {
        $locales = [
            'ar' => ['label' => 'العربية', 'dir' => 'rtl'],
            'en' => ['label' => 'English', 'dir' => 'ltr'],
        ];

        return Tabs::make($field.'_tabs')
            ->tabs(collect($locales)->map(function ($meta, $locale) use ($field, $type, $label) {
                $name = "{$field}.{$locale}";

                $input = match ($type) {
                    'textarea' => Textarea::make($name)->label("{$label} ({$meta['label']})")->rows(4)->extraAttributes(['dir' => $meta['dir']]),
                    'richtext' => RichEditor::make($name)->label("{$label} ({$meta['label']})"),
                    default => TextInput::make($name)->label("{$label} ({$meta['label']})")->extraAttributes(['dir' => $meta['dir']]),
                };

                return Tab::make($locale)->label($meta['label'])->schema([$input]);
            })->values()->all());
    }
}
