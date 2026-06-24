<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\StatisticResource\Pages;
use App\Models\Statistic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatisticResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Statistic::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')->label(__('filament.resources.statistic.column_type'))->options([
                Statistic::TYPE_ACHIEVEMENT => __('filament.resources.statistic.type_achievement'),
                Statistic::TYPE_HUMANITARIAN => __('filament.resources.statistic.type_humanitarian'),
            ])->required(),
            Forms\Components\TextInput::make('value')->label(__('filament.resources.gaza_stat.column_value'))->numeric()->required(),
            Forms\Components\TextInput::make('prefix')->label(__('filament.resources.statistic.prefix'))->placeholder('+'),
            static::localeTabs('label', __('filament.resources.gaza_stat.column_label')),
            Forms\Components\TextInput::make('icon')
                ->label(__('filament.resources.statistic.icon'))
                ->helperText(__('filament.resources.statistic.icon_helper'))
                ->placeholder('hand-holding-heart'),
            Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')->label(__('filament.resources.statistic.column_type'))->badge(),
            Tables\Columns\TextColumn::make('value')->label(__('filament.resources.gaza_stat.column_value')),
            Tables\Columns\TextColumn::make('label')->label(__('filament.resources.gaza_stat.column_label'))->formatStateUsing(fn ($state, $record) => $record?->getTranslation('label', 'ar') ?? ''),
            Tables\Columns\TextColumn::make('icon')
                ->label(__('filament.resources.statistic.icon'))
                ->formatStateUsing(fn ($state) => $state ? $state : '—'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatistics::route('/'),
            'create' => Pages\CreateStatistic::route('/create'),
            'edit' => Pages\EditStatistic::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.statistic.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.statistic.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.statistic.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
