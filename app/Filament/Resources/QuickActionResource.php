<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\QuickActionResource\Pages;
use App\Models\QuickAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuickActionResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = QuickAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('icon')->label(__('filament.resources.quick_action.icon'))->default('💚'),
            static::localeTabs('title', __('filament.pages.manage_site_settings.about_title')),
            static::localeTabs('description', __('filament.resources.quick_action.description'), 'textarea'),
            Forms\Components\TextInput::make('link')->label(__('filament.resources.quick_action.link'))->placeholder('#donate'),
            Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon'),
                Tables\Columns\TextColumn::make('title')->label(__('filament.pages.manage_site_settings.about_title'))->formatStateUsing(fn ($record) => $record->getTranslation('title', 'ar')),
                Tables\Columns\TextColumn::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuickActions::route('/'),
            'create' => Pages\CreateQuickAction::route('/create'),
            'edit' => Pages\EditQuickAction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.quick_action.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.quick_action.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.quick_action.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
