<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Cache;

class DonationChart extends ChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can('view_any_donation') ?? false;
    }

    public function getHeading(): ?string
    {
        return __('filament.widgets.donation_chart.heading');
    }

    protected function getData(): array
    {
        $data = Cache::remember('donation_chart_data', 300, function () {
            return Trend::model(Donation::class)
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => __('filament.widgets.donation_chart.label'),
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#0d6b4f',
                    'borderColor' => '#0d6b4f',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
