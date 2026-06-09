<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\PaymentConfirmationResource\Pages;
use App\Models\PaymentConfirmation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentConfirmationResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = PaymentConfirmation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('donation_id')->relationship('donation', 'id')->required(),
            Forms\Components\TextInput::make('type')->required(),
            Forms\Components\TextInput::make('reference_number'),
            Forms\Components\TextInput::make('amount')->numeric(),
            Forms\Components\TextInput::make('currency'),
            Forms\Components\TextInput::make('sender_name'),
            Forms\Components\TextInput::make('sender_account'),
            Forms\Components\DatePicker::make('transfer_date'),
            Forms\Components\Textarea::make('notes'),
            Forms\Components\TextInput::make('proof_document'),
            Forms\Components\Select::make('status')->options(['pending' => __('filament.resources.donation_submission.status_pending'), 'confirmed' => __('filament.resources.donation_submission.status_confirmed'), 'rejected' => __('filament.resources.volunteer.status_rejected')]),
            Forms\Components\Textarea::make('admin_notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('donation_id')->label(__('filament.resources.payment_confirmation.column_donation'))->sortable(),
            Tables\Columns\TextColumn::make('type')->label(__('filament.resources.statistic.column_type'))->badge(),
            Tables\Columns\TextColumn::make('reference_number')->label(__('filament.resources.payment_confirmation.column_reference'))->searchable(),
            Tables\Columns\TextColumn::make('amount')->label(__('filament.widgets.latest_donations.column_amount'))->money('USD'),
            Tables\Columns\TextColumn::make('sender_name')->label(__('filament.resources.payment_confirmation.column_sender'))->searchable(),
            Tables\Columns\TextColumn::make('status')->label(__('filament.widgets.latest_donations.column_status'))->badge()
                ->color(fn ($state) => match ($state) {
                    'pending' => 'warning', 'confirmed' => 'success', 'rejected' => 'danger', default => 'gray'
                }),
            Tables\Columns\TextColumn::make('created_at')->label(__('filament.resources.payment_confirmation.column_created'))->dateTime(),
        ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['bank_transfer' => __('filament.resources.payment_confirmation.filter_type_bank'), 'crypto' => __('filament.resources.payment_confirmation.filter_type_crypto')]),
                Tables\Filters\SelectFilter::make('status')->options(['pending' => __('filament.resources.donation_submission.status_pending'), 'confirmed' => __('filament.resources.donation_submission.status_confirmed'), 'rejected' => __('filament.resources.volunteer.status_rejected')]),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPaymentConfirmations::route('/'), 'edit' => Pages\EditPaymentConfirmation::route('/{record}/edit')];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.payment_confirmation.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.payment_confirmation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.payment_confirmation.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.campaigns_donations');
    }
}
