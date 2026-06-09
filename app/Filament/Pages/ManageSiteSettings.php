<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasLocaleTabs;
use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ManageSiteSettings extends Page implements HasForms
{
    use HasLocaleTabs;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->can('manage_settings');
    }

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament.pages.manage_site_settings.section.identity'))->schema([
                    static::localeTabs('site_name', __('filament.pages.manage_site_settings.site_name')),
                    static::localeTabs('tagline', __('filament.pages.manage_site_settings.tagline')),
                    static::localeTabs('hero_title', __('filament.pages.manage_site_settings.hero_title')),
                    static::localeTabs('hero_subtitle', __('filament.pages.manage_site_settings.hero_subtitle'), 'textarea'),
                    FileUpload::make('hero_image')->label(__('filament.pages.manage_site_settings.hero_image'))->image()->directory('site')->visibility('public')->nullable()
                        ->afterStateHydrated(function (FileUpload $component, $state) {
                            if (is_string($state) && filled($state)) {
                                $component->state([$state]);
                            }
                        })->removeUploadedFileButtonPosition('right'),
                    FileUpload::make('logo')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('site')->visibility('public')->nullable()
                        ->afterStateHydrated(function (FileUpload $component, $state) {
                            if (is_string($state) && filled($state)) {
                                $component->state([$state]);
                            }
                        })->removeUploadedFileButtonPosition('right'),
                    Tabs::make('logos_tabs')->label(__('filament.pages.manage_site_settings.per_language_logos'))->columnSpanFull()
                        ->tabs([
                            Tab::make('ar')->label('العربية')->schema([
                                FileUpload::make('logos.ar')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('site/logos')->visibility('public')->nullable()
                                    ->afterStateHydrated(function (FileUpload $component, $state) {
                                        if (is_string($state) && filled($state)) {
                                            $component->state([$state]);
                                        }
                                    })
                                    ->removeUploadedFileButtonPosition('right'),
                            ]),
                            Tab::make('en')->label('English')->schema([
                                FileUpload::make('logos.en')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('site/logos')->visibility('public')->nullable()
                                    ->afterStateHydrated(function (FileUpload $component, $state) {
                                        if (is_string($state) && filled($state)) {
                                            $component->state([$state]);
                                        }
                                    })
                                    ->removeUploadedFileButtonPosition('right'),
                            ]),
                            Tab::make('es')->label('Español')->schema([
                                FileUpload::make('logos.es')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('site/logos')->visibility('public')->nullable()
                                    ->afterStateHydrated(function (FileUpload $component, $state) {
                                        if (is_string($state) && filled($state)) {
                                            $component->state([$state]);
                                        }
                                    })
                                    ->removeUploadedFileButtonPosition('right'),
                            ]),
                            Tab::make('id')->label('Bahasa Indonesia')->schema([
                                FileUpload::make('logos.id')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('site/logos')->visibility('public')->nullable()
                                    ->afterStateHydrated(function (FileUpload $component, $state) {
                                        if (is_string($state) && filled($state)) {
                                            $component->state([$state]);
                                        }
                                    })
                                    ->removeUploadedFileButtonPosition('right'),
                            ]),
                            Tab::make('tr')->label('Türkçe')->schema([
                                FileUpload::make('logos.tr')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('site/logos')->visibility('public')->nullable()
                                    ->afterStateHydrated(function (FileUpload $component, $state) {
                                        if (is_string($state) && filled($state)) {
                                            $component->state([$state]);
                                        }
                                    })
                                    ->removeUploadedFileButtonPosition('right'),
                            ]),
                            Tab::make('sv')->label('Svenska')->schema([
                                FileUpload::make('logos.sv')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('site/logos')->visibility('public')->nullable()
                                    ->afterStateHydrated(function (FileUpload $component, $state) {
                                        if (is_string($state) && filled($state)) {
                                            $component->state([$state]);
                                        }
                                    })
                                    ->removeUploadedFileButtonPosition('right'),
                            ]),
                        ]),
                ])->columns(1),

                Section::make(__('filament.pages.manage_site_settings.section.about'))->schema([
                    static::localeTabs('about_title', __('filament.pages.manage_site_settings.about_title')),
                    static::localeTabs('about_content', __('filament.pages.manage_site_settings.about_content'), 'textarea'),
                    FileUpload::make('about_image')->label(__('filament.pages.manage_site_settings.about_image'))->image()->directory('site')->visibility('public')->nullable()
                        ->afterStateHydrated(function (FileUpload $component, $state) {
                            if (is_string($state) && filled($state)) {
                                $component->state([$state]);
                            }
                        })->removeUploadedFileButtonPosition('right'),
                ]),

                Section::make(__('filament.pages.manage_site_settings.section.donations'))->schema([
                    static::localeTabs('donate_title', __('filament.pages.manage_site_settings.donate_title')),
                    static::localeTabs('donate_description', __('filament.pages.manage_site_settings.donate_description'), 'textarea'),
                    static::localeTabs('footer_description', __('filament.pages.manage_site_settings.footer_description'), 'textarea'),
                ]),

                Section::make(__('filament.pages.manage_site_settings.section.contact'))->schema([
                    TextInput::make('phone')->label(__('filament.pages.manage_site_settings.phone'))->tel(),
                    TextInput::make('email')->label(__('filament.pages.manage_site_settings.email'))->email(),
                    TextInput::make('whatsapp')->label(__('filament.pages.manage_site_settings.whatsapp')),
                    TextInput::make('twitter')->label(__('filament.pages.manage_site_settings.twitter')),
                    TextInput::make('facebook')->label(__('filament.pages.manage_site_settings.facebook')),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $setting = SiteSetting::current();
        $old = $setting->replicate();

        $data = $this->form->getState();
        $setting->update($data);

        $imageFields = ['hero_image', 'logo', 'about_image'];
        foreach ($imageFields as $field) {
            $oldPath = $old->{$field};
            $newPath = $data[$field] ?? null;
            if ($oldPath && $oldPath !== $newPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $oldLogos = $old->logos ?? [];
        $newLogos = $data['logos'] ?? [];
        foreach ($oldLogos as $locale => $path) {
            if ($path && (! isset($newLogos[$locale]) || $newLogos[$locale] !== $path)) {
                Storage::disk('public')->delete($path);
            }
        }

        Notification::make()
            ->title(__('filament.pages.manage_site_settings.saved'))
            ->success()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.pages.manage_site_settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('filament.pages.manage_site_settings.title');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.settings');
    }
}
