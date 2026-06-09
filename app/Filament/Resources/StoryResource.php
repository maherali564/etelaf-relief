<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\StoryResource\Pages;
use App\Models\Story;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class StoryResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Story::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('content', __('filament.pages.manage_site_settings.about_content'), 'richtext'),
            Forms\Components\TextInput::make('person_name')->label(__('filament.resources.story.column_person')),
            Forms\Components\TextInput::make('age')->label(__('filament.resources.story.age'))->numeric(),
            Forms\Components\TextInput::make('location')->label(__('filament.resources.story.location')),
            Forms\Components\TextInput::make('goal_amount')->label(__('filament.resources.story.goal_amount'))->numeric()->default(0)->prefix('$'),
            Forms\Components\TextInput::make('raised_amount')->label(__('filament.resources.story.raised_amount'))->numeric()->default(0)->prefix('$'),
            Forms\Components\Section::make(__('filament.resources.story.section.images'))
                ->schema([
                    FileUpload::make('image')->label(__('filament.resources.story.image'))->image()->directory('stories')->nullable()
                        ->afterStateHydrated(function (FileUpload $component, $state) {
                            if (is_string($state) && filled($state)) {
                                $component->state([$state]);
                            }
                        })
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.story.image_hint')),
                    FileUpload::make('images')
                        ->label(__('filament.resources.story.images'))
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->directory('stories')
                        ->nullable()
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.story.images_hint')),
                ])->columns(1),
            Forms\Components\TextInput::make('video_url')->label(__('filament.resources.story.video_url'))->url()->placeholder('https://youtube.com/watch?v=... or /storage/videos/...')->nullable(),
            Forms\Components\Select::make('video_type')->label(__('filament.resources.story.video_type'))->options([
                'youtube' => 'YouTube',
                'vimeo' => 'Vimeo',
                'upload' => __('filament.resources.story.video_type_upload'),
            ])->placeholder(__('filament.resources.story.video_type_auto'))->nullable(),
            Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label(__('filament.resources.slider.column_image')),
            Tables\Columns\TextColumn::make('title')->label(__('filament.pages.manage_site_settings.about_title'))->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
            Tables\Columns\TextColumn::make('person_name')->label(__('filament.resources.story.column_person')),
            Tables\Columns\TextColumn::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
        ])->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStories::route('/'),
            'create' => Pages\CreateStory::route('/create'),
            'edit' => Pages\EditStory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.story.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.story.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.story.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
