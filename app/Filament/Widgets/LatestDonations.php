<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

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
                Tables\Columns\TextColumn::make('donor_name')->label(__('filament.widgets.latest_donations.column_donor')),
                Tables\Columns\TextColumn::make('amount')->label(__('filament.widgets.latest_donations.column_amount'))->money('USD'),
                Tables\Columns\TextColumn::make('status')->label(__('filament.widgets.latest_donations.column_status'))->badge(),
                Tables\Columns\TextColumn::make('created_at')->label(__('filament.widgets.latest_donations.column_date'))->dateTime(),
            ]);
    }
}
