<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('gateway_id')->label(__('filament.resources.donation.filter_gateway'))
                ->relationship('gateway', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->helperText(__('filament.resources.payment_method.gateway_hint')),
            Forms\Components\Section::make(__('filament.resources.payment_method.section.info'))->schema([
                Forms\Components\TextInput::make('name')->label(__('filament.resources.donation_submission.column_name'))->required(),
                Forms\Components\Textarea::make('description')->label(__('filament.resources.quick_action.description')),
                Forms\Components\Textarea::make('account_info')->label(__('filament.resources.payment_method.account_info')),
                Forms\Components\Textarea::make('instructions')->label(__('filament.resources.payment_method.instructions'))
                    ->helperText(__('filament.resources.payment_method.instructions_hint')),
                Forms\Components\TextInput::make('icon')->label(__('filament.resources.quick_action.column_icon'))->placeholder('💳'),
                Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label(__('filament.resources.donation_submission.column_name')),
            Tables\Columns\TextColumn::make('gateway.name')->label(__('filament.resources.donation.filter_gateway'))->badge()->color('info'),
            Tables\Columns\TextColumn::make('description')->label(__('filament.resources.quick_action.description'))->limit(50),
            Tables\Columns\TextColumn::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
        ])->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.payment_method.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.payment_method.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.payment_method.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.campaigns_donations');
    }
}
