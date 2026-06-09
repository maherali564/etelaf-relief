<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ProjectResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')->label(__('filament.resources.project.slug')),
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('description', __('filament.resources.project.description'), 'textarea'),
            static::localeTabs('content', __('filament.resources.project.content'), 'richtext'),
            Forms\Components\Section::make(__('filament.resources.project.section.images'))
                ->schema([
                    FileUpload::make('image')->label(__('filament.resources.story.image'))->image()->directory('projects')->visibility('public')->nullable()
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
                        ->directory('projects')
                        ->nullable()
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.story.images_hint')),
                ])->columns(1),
            Forms\Components\TextInput::make('video_url')->label(__('filament.resources.project.video_url'))->url()->placeholder('https://youtube.com/watch?v=... or /storage/videos/...')->nullable(),
            Forms\Components\Select::make('video_type')->label(__('filament.resources.project.video_type'))->options([
                'youtube' => 'YouTube',
                'vimeo' => 'Vimeo',
                'upload' => __('filament.resources.project.video_type_upload'),
            ])->placeholder(__('filament.resources.project.video_type_auto'))->nullable(),
            Forms\Components\Section::make(__('filament.resources.project.section.donation_counter'))
                ->schema([
                    Forms\Components\TextInput::make('goal_amount')->label(__('filament.resources.project.goal_amount'))->numeric()->default(0)->prefix('$'),
                    Forms\Components\TextInput::make('raised_amount')->label(__('filament.resources.project.raised_amount'))->numeric()->default(0)->prefix('$'),
                ])->columns(2),
            Forms\Components\Toggle::make('is_featured')->label(__('filament.resources.project.is_featured')),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label(__('filament.resources.slider.column_image')),
            Tables\Columns\TextColumn::make('title')->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
            Tables\Columns\IconColumn::make('is_featured')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.project.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.pages_projects');
    }
}
