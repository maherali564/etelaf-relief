<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label(__('filament.resources.donation_submission.column_name'))->required(),
            Forms\Components\TextInput::make('email')->label(__('filament.resources.newsletter.column_email'))->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('password')->label(__('filament.resources.user.password'))->password()->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn ($state): bool => filled($state)),
            Forms\Components\Select::make('roles')
                ->label(__('filament.resources.user.column_role'))
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->options(Role::all()->pluck('name', 'id')
                    ->map(fn ($name) => __("filament.resources.role.{$name}")))
                ->searchable()
                ->afterStateUpdated(function (callable $set, $state) {
                    if (! empty($state) && Role::whereIn('id', $state)->whereIn('name', ['super_admin', 'admin', 'supporter'])->exists()) {
                        $set('can_chat', true);
                    }
                }),
            Forms\Components\Toggle::make('is_admin')->label(__('filament.resources.user.is_admin')),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
            Forms\Components\Toggle::make('can_chat')->label(__('filament.resources.user.can_chat'))->helperText(__('filament.resources.user.can_chat_hint')),
            Forms\Components\TextInput::make('phone')->label(__('filament.resources.volunteer.column_phone')),
            FileUpload::make('avatar')->label(__('filament.resources.user.avatar'))->image()->directory('avatars')->nullable()
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    if (is_string($state) && filled($state)) {
                        $component->state([$state]);
                    }
                })
                ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                ->removeUploadedFileButtonPosition('right'),
            Forms\Components\Select::make('preferred_locale')->label(__('filament.resources.user.preferred_locale'))->default('ar')->options([
                'ar' => __('filament.resources.user.locale_ar'),
                'en' => __('filament.resources.user.locale_en'),
                'es' => __('filament.resources.user.locale_es'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label(__('filament.resources.donation_submission.column_name'))->searchable(),
            Tables\Columns\TextColumn::make('email')->label(__('filament.resources.newsletter.column_email'))->searchable(),
            Tables\Columns\TextColumn::make('role')->label(__('filament.resources.user.column_role'))->badge()->color(fn (string $state) => match ($state) {
                'super_admin' => 'danger',
                'admin' => 'warning',
                'editor' => 'info',
                'donation_manager' => 'success',
                'supporter' => 'gray',
                default => 'gray',
            }),
            Tables\Columns\TextColumn::make('roles.name')
                ->label(__('filament.resources.user.spatie_roles'))
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'super_admin' => 'danger',
                    'admin' => 'warning',
                    'editor' => 'info',
                    'donation_manager' => 'success',
                    default => 'primary',
                }),
            Tables\Columns\IconColumn::make('is_admin')->label(__('filament.resources.user.column_admin'))->boolean(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
            Tables\Columns\IconColumn::make('can_chat')->label(__('filament.resources.user.column_chat'))->boolean(),
            Tables\Columns\TextColumn::make('created_at')->label(__('filament.resources.volunteer.column_created'))->dateTime(),
        ])->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.user.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.user.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.user.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.users');
    }
}
