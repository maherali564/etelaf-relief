<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::localeTabs('question', __('filament.resources.faq.column_question')),
            static::localeTabs('answer', __('filament.resources.faq.answer'), 'textarea'),
            Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('question')->label(__('filament.resources.faq.column_question'))->formatStateUsing(fn ($state, $record) => $record?->getTranslation('question', 'ar') ?? ''),
            Tables\Columns\TextColumn::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
        ])->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.faq.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.faq.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.faq.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.content');
    }
}
