<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\URL;

class LatestDonations extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('view_any_donation') ?? false;
    }

    public function getHeading(): ?string
    {
        return __('filament.widgets.latest_donations.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Donation::latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('donor_name')
                    ->label(__('filament.widgets.latest_donations.column_donor'))
                    ->url(fn ($record) => URL::route('filament.admin.resources.donations.edit', $record))
                    ->color('primary'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.resources.newsletter.column_email'))
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('filament.widgets.latest_donations.column_amount'))
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label(__('filament.resources.donation.column_method'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.widgets.latest_donations.column_status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'under_review' => 'info',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.widgets.latest_donations.column_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->poll('30s');
    }
}
