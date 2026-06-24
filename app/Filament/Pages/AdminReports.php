<?php

namespace App\Filament\Pages;

use App\Models\Donation;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->can('view_any_donation') || $user->hasRole('super_admin'));
    }

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Donation Reports';

    protected static ?string $slug = 'reports';

    protected static string $view = 'filament.pages.admin-reports';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?string $period = 'monthly';

    public function mount(): void
    {
        $this->dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function getStats(): array
    {
        return Cache::remember("admin_reports_stats_{$this->dateFrom}_{$this->dateTo}", 300, function () {
            $query = Donation::completed()->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);

            $total = $query->sum('amount');
            $donors = $query->distinct('email')->count('email');
            $count = $query->count();
            $avg = $count > 0 ? $total / $count : 0;

            $stats = [];

            $s1 = new Stat(
                'Total Donations',
                number_format($total, 0).' $',
                'Completed donations in period'
            );
            $s1->icon('heroicon-m-currency-dollar')->color('success');
            $stats[] = $s1;

            $s2 = new Stat(
                'Donors',
                (string) $donors,
                'Unique donors'
            );
            $s2->icon('heroicon-m-user-group')->color('info');
            $stats[] = $s2;

            $s3 = new Stat(
                'Transactions',
                (string) $count,
                'Number of donations'
            );
            $s3->icon('heroicon-m-shopping-cart')->color('warning');
            $stats[] = $s3;

            $s4 = new Stat(
                'Average',
                number_format($avg, 2).' $',
                'Per donation'
            );
            $s4->icon('heroicon-m-calculator')->color('danger');
            $stats[] = $s4;

            return $stats;
        });
    }

    public function getChartData(): array
    {
        return Cache::remember("admin_reports_chart_{$this->dateFrom}_{$this->dateTo}_{$this->period}", 300, function () {
            $query = Donation::completed()
                ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                ->select(
                    DB::raw($this->period === 'yearly' ? "strftime('%Y', created_at) as period" : "strftime('%Y-%m', created_at) as period"),
                    DB::raw('SUM(amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return [
                'labels' => $query->pluck('period')->toArray(),
                'amounts' => $query->pluck('total')->toArray(),
                'counts' => $query->pluck('count')->toArray(),
            ];
        });
    }

    public function getMethodBreakdown(): array
    {
        return Cache::remember("admin_reports_methods_{$this->dateFrom}_{$this->dateTo}", 300, function () {
            return Donation::completed()
                ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                ->select('payment_method_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->with('paymentMethod')
                ->groupBy('payment_method_id')
                ->get()
                ->map(fn ($d) => [
                    'method' => $d->paymentMethod?->name ?? 'Unknown',
                    'total' => (float) $d->total,
                    'count' => (int) $d->count,
                ])
                ->toArray();
        });
    }

    public function getProjectBreakdown(): array
    {
        return Cache::remember("admin_reports_projects_{$this->dateFrom}_{$this->dateTo}", 300, function () {
            return Donation::completed()
                ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                ->whereNotNull('project_id')
                ->select('project_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->with('project')
                ->groupBy('project_id')
                ->get()
                ->map(fn ($d) => [
                    'project' => $d->project ? trans_field($d->project, 'title') : 'Unknown',
                    'total' => (float) $d->total,
                    'count' => (int) $d->count,
                ])
                ->toArray();
        });
    }

    protected function getForms(): array
    {
        return [];
    }
}
