<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CampaignResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('description', __('filament.resources.quick_action.description'), 'textarea'),
            Forms\Components\TextInput::make('goal_amount')->label(__('filament.resources.project.goal_amount'))->numeric()->required(),
            Forms\Components\TextInput::make('raised_amount')->label(__('filament.resources.project.raised_amount'))->numeric()->default(0),
            Forms\Components\Section::make(__('filament.resources.campaign.section.images'))
                ->schema([
                    FileUpload::make('image')->label(__('filament.resources.story.image'))->image()->directory('campaigns')->nullable()
                        ->afterStateHydrated(function (FileUpload $component, $state) {
                            if (is_string($state) && filled($state)) {
                                $component->state([$state]);
                            }
                        })
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.campaign.image_hint')),
                    FileUpload::make('images')
                        ->label(__('filament.resources.story.images'))
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->directory('campaigns')
                        ->nullable()
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->removeUploadedFileButtonPosition('right')
                        ->helperText(__('filament.resources.campaign.images_hint')),
                ])->columns(1),
            Forms\Components\TextInput::make('slug')->label(__('filament.resources.campaign.slug')),
            Forms\Components\DatePicker::make('start_date')->label(__('filament.resources.campaign.column_start')),
            Forms\Components\DatePicker::make('end_date')->label(__('filament.resources.campaign.column_end')),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label(__('filament.pages.manage_site_settings.about_title'))->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
            Tables\Columns\TextColumn::make('goal_amount')->label(__('filament.resources.campaign.column_goal'))->money('USD'),
            Tables\Columns\TextColumn::make('raised_amount')->label(__('filament.resources.campaign.column_raised'))->money('USD'),
            Tables\Columns\TextColumn::make('start_date')->label(__('filament.resources.campaign.column_start'))->date(),
            Tables\Columns\TextColumn::make('end_date')->label(__('filament.resources.campaign.column_end'))->date(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
        ])->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.campaign.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.campaign.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.campaign.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.campaigns_donations');
    }
}
