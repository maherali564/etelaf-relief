<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\CryptocurrencyResource\Pages;
use App\Models\Cryptocurrency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CryptocurrencyResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Cryptocurrency::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('symbol')->required()->maxLength(10),
            Forms\Components\FileUpload::make('logo')->image()->directory('crypto')->nullable()->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file)),
            Forms\Components\TextInput::make('min_amount')->numeric()->step(0.00000001)->default(0),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Section::make(__('filament.resources.cryptocurrency.section.networks'))->schema([
                Forms\Components\Repeater::make('networks')
                    ->relationship('networks')
                    ->schema([
                        Forms\Components\TextInput::make('network_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('wallet_address')->required()->maxLength(255),
                        Forms\Components\FileUpload::make('qr_code')->image()->directory('crypto-qr')->nullable()->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file)),
                        Forms\Components\Toggle::make('is_active')->default(true),
                    ])->columns(2)->defaultItems(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('symbol')->searchable(),
            Tables\Columns\TextColumn::make('min_amount')->label(__('filament.resources.cryptocurrency.column_min')),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('networks_count')->counts('networks')->label(__('filament.resources.cryptocurrency.column_networks')),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCryptocurrencies::route('/'),
            'create' => Pages\CreateCryptocurrency::route('/create'),
            'edit' => Pages\EditCryptocurrency::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.cryptocurrency.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.campaigns_donations');
    }
}
