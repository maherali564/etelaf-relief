<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('filament.resources.permission.name'))
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\Hidden::make('guard_name')->default('web'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label(__('filament.resources.permission.name'))
                ->searchable()
                ->formatStateUsing(fn ($state) => __("filament.permissions.{$state}")),
            Tables\Columns\TextColumn::make('guard_name')
                ->label(__('filament.resources.permission.guard')),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('filament.resources.volunteer.column_created'))
                ->dateTime(),
        ])->defaultSort('created_at', 'asc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.permission.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.permission.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.permission.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.users');
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }
}
