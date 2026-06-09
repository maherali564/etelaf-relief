<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\DonationSubmissionResource\Pages;
use App\Models\DonationSubmission;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DonationSubmissionResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = DonationSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            Tables\Columns\TextColumn::make('amount')->money('USD'),
            Tables\Columns\SelectColumn::make('status')->options([
                'pending' => __('filament.resources.donation_submission.status_pending'),
                'confirmed' => __('filament.resources.donation_submission.status_confirmed'),
                'cancelled' => __('filament.resources.donation_submission.status_cancelled'),
            ]),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonationSubmissions::route('/'),
            'edit' => Pages\EditDonationSubmission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.donation_submission.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.messages');
    }
}
