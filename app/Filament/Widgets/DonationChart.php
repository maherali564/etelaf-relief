<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Cache;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 الودجت: DonationChart
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    عرض رسم بياني خطي لعدد التبرعات الشهرية أو إجمالي
 *    المبالغ على مدار العام الحالي.
 *    يستخدم حزمة Flowframe\Trend للتجميع مع تخزين مؤقت
 *    (Cache) لمدة 5 دقائق لتحسين الأداء.
 * ──────────────────────────────────────────────────────────────
 */
class DonationChart extends ChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can('view_any_donation') ?? false;
    }

    public function getHeading(): ?string
    {
        $metric = $this->filter === 'sum'
            ? __('filament.widgets.donation_chart.sum_label')
            : __('filament.widgets.donation_chart.label');

        return __('filament.widgets.donation_chart.heading').' — '.$metric;
    }

    protected function getFilters(): ?array
    {
        return [
            'count' => __('filament.widgets.donation_chart.count_label'),
            'sum' => __('filament.widgets.donation_chart.sum_label'),
        ];
    }

    protected function getData(): array
    {
        $cacheKey = 'donation_chart_data_'.$this->filter;

        $data = Cache::remember($cacheKey, 300, function () {
            $trend = Trend::model(Donation::class)
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth();

            return $this->filter === 'sum'
                ? $trend->sum('amount')
                : $trend->count();
        });

        return [
            'datasets' => [
                [
                    'label' => $this->filter === 'sum'
                        ? __('filament.widgets.donation_chart.sum_label')
                        : __('filament.widgets.donation_chart.label'),
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
