<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\GazaStatResource\Pages;
use App\Models\GazaStat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GazaStatResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = GazaStat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::localeTabs('label', __('filament.resources.gaza_stat.column_label')),
            Forms\Components\TextInput::make('value')->label(__('filament.resources.gaza_stat.column_value'))->required(),
            Forms\Components\TextInput::make('prefix')->label(__('filament.resources.gaza_stat.prefix'))->placeholder('+'),
            Forms\Components\TextInput::make('icon')->label(__('filament.resources.gaza_stat.icon'))->default('❤️'),
            Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('value')->label(__('filament.resources.gaza_stat.column_value')),
            Tables\Columns\TextColumn::make('prefix')->label(__('filament.resources.gaza_stat.column_prefix')),
            Tables\Columns\TextColumn::make('label')->label(__('filament.resources.gaza_stat.column_label'))->formatStateUsing(fn ($state, $record) => $record?->getTranslation('label', 'ar') ?? ''),
            Tables\Columns\IconColumn::make('is_active')->boolean()->label(__('filament.resources.user.column_active')),
        ])->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGazaStats::route('/'),
            'create' => Pages\CreateGazaStat::route('/create'),
            'edit' => Pages\EditGazaStat::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.gaza_stat.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.gaza_stat.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.gaza_stat.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
