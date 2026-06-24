<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\MediaItemResource\Pages;
use App\Models\MediaItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MediaItemResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = MediaItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label(__('filament.resources.media_item.type'))
                ->options([
                    'image' => __('filament.resources.media_item.type_image'),
                    'video' => __('filament.resources.media_item.type_video'),
                ])->required(),
            Forms\Components\FileUpload::make('image')
                ->label(__('filament.resources.media_item.image'))
                ->image()
                ->directory('media')
                ->visible(fn ($get) => $get('type') === 'image'),
            Forms\Components\TextInput::make('video_url')
                ->label(__('filament.resources.media_item.video_url'))
                ->url()
                ->visible(fn ($get) => $get('type') === 'video'),
            Forms\Components\Select::make('video_platform')
                ->label(__('filament.resources.media_item.video_platform'))
                ->options(['youtube' => 'YouTube', 'vimeo' => 'Vimeo'])
                ->visible(fn ($get) => $get('type') === 'video'),
            Forms\Components\FileUpload::make('thumbnail')
                ->label(__('filament.resources.media_item.thumbnail'))
                ->image()
                ->directory('media/thumbnails'),
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('description', __('filament.resources.quick_action.description'), 'textarea'),
            Forms\Components\TextInput::make('url')->label(__('filament.resources.media_item.url')),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\ImageColumn::make('thumbnail')
                ->label(__('filament.resources.media_item.thumbnail'))
                ->defaultImageUrl(fn ($record) => $record->type === 'image' && $record->image ? asset('storage/'.$record->image) : ''),
            Tables\Columns\TextColumn::make('title')->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaItems::route('/'),
            'create' => Pages\CreateMediaItem::route('/create'),
            'edit' => Pages\EditMediaItem::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.media_item.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.media_item.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.media_item.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
