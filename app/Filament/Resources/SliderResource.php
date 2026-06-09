<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class SliderResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('image')->label(__('filament.resources.testimonial.image'))->image()->directory('slider')->disk('public')->visibility('public')->nullable()
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    if (is_string($state) && filled($state)) {
                        $component->state([$state]);
                    }
                })
                ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                ->removeUploadedFileButtonPosition('right')
                ->panelLayout('integrated')
                ->uploadButtonPosition('right')
                ->uploadProgressIndicatorPosition('right'),
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('subtitle', __('filament.resources.slider.subtitle'), 'textarea'),
            static::localeTabs('button_text', __('filament.resources.slider.button_text')),
            Forms\Components\TextInput::make('button_link')->label(__('filament.resources.slider.button_link')),
            Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label(__('filament.resources.slider.column_image')),
            Tables\Columns\TextColumn::make('title')->label(__('filament.pages.manage_site_settings.about_title'))->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
            Tables\Columns\TextColumn::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
        ])->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.slider.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.slider.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.slider.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
