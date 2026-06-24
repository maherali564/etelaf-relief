<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\Program;
use App\Services\VideoService;
use Filament\Forms;
use Illuminate\Support\Facades\Log;
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
            static::localeTabs('location', __('filament.resources.project.location')),
            Forms\Components\DatePicker::make('start_date')->label(__('filament.resources.project.start_date'))->nullable(),
            Forms\Components\DatePicker::make('end_date')->label(__('filament.resources.project.end_date'))->nullable(),
            Forms\Components\Select::make('program_id')
                ->label(__('filament.resources.project.program'))
                ->options(Program::active()->pluck('title', 'id'))
                ->searchable()
                ->nullable()
                ->helperText(__('filament.resources.project.program_hint')),
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('description', __('filament.resources.project.description'), 'textarea'),
            static::localeTabs('content', __('filament.resources.project.content'), 'richtext'),
            Forms\Components\Section::make(__('filament.resources.project.section.images'))
                ->schema([
                    FileUpload::make('image')->label(__('filament.resources.story.image'))->image()->disk('public')->directory('projects')->visibility('public')->nullable()
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
                        ->disk('public')
                        ->directory('projects')
                        ->nullable()
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.story.images_hint')),
                ])->columns(1),
            Forms\Components\Section::make(__('filament.resources.project.section.videos'))
                ->schema([
                    Forms\Components\TextInput::make('video_url')->label(__('filament.resources.project.video_url'))->url()->placeholder('https://youtube.com/watch?v=...')->nullable(),
                    Forms\Components\Select::make('video_type')->label(__('filament.resources.project.video_type'))->options([
                        'youtube' => 'YouTube',
                        'vimeo' => 'Vimeo',
                        'upload' => __('filament.resources.project.video_type_upload'),
                    ])->placeholder(__('filament.resources.project.video_type_auto'))->nullable(),
                    FileUpload::make('videos')
                        ->label(__('filament.resources.project.videos'))
                        ->multiple()
                        ->reorderable()
                        ->disk('public')
                        ->directory('projects/videos')
                        ->acceptedFileTypes(['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'])
                        ->maxSize(2048000)
                        ->imagePreviewHeight('200')
                        ->nullable()
                        ->afterStateUpdated(function ($state, $record, $component, $set, $get) {
                            if (!$record || !$state || !is_array($state) || count($state) === 0) return;
                            $oldVideos = array_filter($record->videos ?? []);
                            $newVideos = array_values(array_filter($state));
                            if (count($newVideos) <= count($oldVideos)) return;
                            try {
                                $videoService = app(VideoService::class);
                                if (!$videoService->isAvailable()) { $set('video_status', 'completed'); return; }
                                $lastPath = last($newVideos);
                                $fullPath = storage_path('app/public/' . $lastPath);
                                $result = $videoService->processFromPath($fullPath, 'public');
                                $set('video_status', $result['video_status']);
                                $currentVideos = $get('videos') ?? [];
                                $idx = count($currentVideos) - 1;
                                if ($idx >= 0 && $result['video']) { $currentVideos[$idx] = $result['video']; $set('videos', $currentVideos); }
                            } catch (\Throwable $e) {
                                Log::error('Video processing failed', ['error' => $e->getMessage()]);
                                $set('video_status', 'failed');
                            }
                        })
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.project.videos_hint')),
                ])->columns(1),
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
            Tables\Columns\ImageColumn::make('video_thumbnail')
                ->label(__('filament.resources.project.video'))
                ->disk('public')
                ->width(80)
                ->visible(fn ($record) => $record && $record->video_thumbnail),
            Tables\Columns\TextColumn::make('title')->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
            Tables\Columns\TextColumn::make('video_status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'completed' => 'success',
                    'processing' => 'warning',
                    'failed' => 'danger',
                    default => 'gray',
                }),
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
