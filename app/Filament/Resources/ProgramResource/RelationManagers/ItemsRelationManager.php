<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Filament\Concerns\HasLocaleTabs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('icon')
                ->label(__('filament.resources.program_item.icon'))
                ->helperText(__('filament.resources.statistic.icon_helper'))
                ->placeholder('hand-holding-heart'),
            static::localeTabs('title', __('filament.resources.quick_action.title')),
            static::localeTabs('description', __('filament.resources.quick_action.description'), 'textarea'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon'),
                Tables\Columns\TextColumn::make('title')->formatStateUsing(fn ($state, $record) => $record?->getTranslation('title', 'ar') ?? ''),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order'),
            ])
            ->defaultSort('sort_order')
            ->filters([])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
