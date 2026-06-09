<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PostResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')->label(__('filament.resources.statistic.column_type'))->options([
                Post::TYPE_ANNOUNCEMENT => __('filament.resources.post.type_announcement'),
                Post::TYPE_SUCCESS_STORY => __('filament.resources.post.type_success_story'),
                Post::TYPE_NEWS => __('filament.resources.post.type_news'),
            ])->required(),
            Forms\Components\TextInput::make('slug')->label(__('filament.resources.post.slug')),
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('excerpt', __('filament.resources.post.excerpt'), 'textarea'),
            static::localeTabs('content', __('filament.pages.manage_site_settings.about_content'), 'richtext'),
            Forms\Components\Section::make(__('filament.resources.post.section.images'))
                ->schema([
                    FileUpload::make('image')->image()->directory('posts')->visibility('public')->nullable()
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
                        ->directory('posts')
                        ->nullable()
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.story.images_hint')),
                ])->columns(1),
            Forms\Components\TextInput::make('video_url')->label(__('filament.resources.post.video_url'))->url()->placeholder('https://youtube.com/watch?v=... or /storage/videos/...')->nullable(),
            Forms\Components\Select::make('video_type')->label(__('filament.resources.post.video_type'))->options([
                'youtube' => 'YouTube',
                'vimeo' => 'Vimeo',
                'upload' => __('filament.resources.post.video_type_upload'),
            ])->placeholder(__('filament.resources.post.video_type_auto'))->nullable(),
            Forms\Components\Select::make('campaign_id')
                ->label(__('filament.resources.post.campaign'))
                ->relationship('campaign', 'title')
                ->nullable()
                ->searchable()
                ->helperText(__('filament.resources.post.campaign_hint')),
            Forms\Components\DateTimePicker::make('published_at')->label(__('filament.resources.post.column_published')),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('title')->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
            Tables\Columns\TextColumn::make('published_at')->dateTime(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->defaultSort('published_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.post.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
