<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\CryptoNetworkResource\Pages;
use App\Models\CryptoNetwork;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CryptoNetworkResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = CryptoNetwork::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('cryptocurrency_id')
                ->relationship('cryptocurrency', 'name')
                ->required()->searchable(),
            Forms\Components\TextInput::make('network_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('wallet_address')->required()->maxLength(255),
            Forms\Components\FileUpload::make('qr_code')->image()->directory('crypto-qr')->nullable()->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file)),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('cryptocurrency.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('network_name')->searchable(),
            Tables\Columns\TextColumn::make('wallet_address')->limit(20)->copyable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCryptoNetworks::route('/'),
            'create' => Pages\CreateCryptoNetwork::route('/create'),
            'edit' => Pages\EditCryptoNetwork::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.crypto_network.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.campaigns_donations');
    }
}
