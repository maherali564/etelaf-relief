<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('filament.resources.role.name'))
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\Hidden::make('guard_name')->default('web'),
            Forms\Components\Section::make(__('filament.resources.role.permissions'))
                ->schema([
                    Forms\Components\Select::make('permissions')
                        ->label('')
                        ->relationship('permissions', 'name')
                        ->multiple()
                        ->preload()
                        ->options(
                            Permission::all()
                                ->pluck('name', 'id')
                                ->map(fn ($name) => __("filament.permissions.{$name}"))
                        )
                        ->searchable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label(__('filament.resources.role.name'))
                ->searchable()
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'super_admin' => 'danger',
                    'admin' => 'warning',
                    'editor' => 'info',
                    'donation_manager' => 'success',
                    'supporter' => 'gray',
                    default => 'primary',
                }),
            Tables\Columns\TextColumn::make('permissions_count')
                ->label(__('filament.resources.role.permissions_count'))
                ->counts('permissions')
                ->badge(),
            Tables\Columns\TextColumn::make('users_count')
                ->label(__('filament.resources.role.users_count'))
                ->counts('users')
                ->badge(),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('filament.resources.volunteer.column_created'))
                ->dateTime(),
        ])->defaultSort('created_at', 'asc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.role.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.role.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.role.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.users');
    }

    public static function getNavigationSort(): ?int
    {
        return 99;
    }
}
