<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\NewsletterResource\Pages;
use App\Models\Newsletter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Newsletter::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')->label(__('filament.resources.newsletter.column_email')),
            Forms\Components\Toggle::make('is_subscribed')->label(__('filament.resources.newsletter.column_subscribed')),
            Forms\Components\DateTimePicker::make('subscribed_at')->label(__('filament.resources.newsletter.column_date')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('email')->label(__('filament.resources.newsletter.column_email'))->searchable(),
            Tables\Columns\IconColumn::make('is_subscribed')->label(__('filament.resources.newsletter.column_subscribed'))->boolean(),
            Tables\Columns\TextColumn::make('subscribed_at')->label(__('filament.resources.newsletter.column_date'))->dateTime(),
        ])->defaultSort('subscribed_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletters::route('/'),
            'edit' => Pages\EditNewsletter::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.newsletter.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.newsletter.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.newsletter.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.users');
    }
}
