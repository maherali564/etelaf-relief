<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use App\Models\User;
use App\Models\Volunteer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 الودجت: DonationOverview
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    عرض ملخص إحصائي سريع في لوحة التحكم يحتوي على:
 *      - إجمالي التبرعات المكتملة (بـ $)
 *      - تبرعات اليوم
 *      - عدد المتبرعين الفريدين
 *      - التبرعات المعلقة للمراجعة
 *      - عدد المستخدمين المسجلين
 *      - عدد المتطوعين
 *    يتم تخزين النتيجة في Cache لمدة 5 دقائق لتحسين الأداء.
 * ──────────────────────────────────────────────────────────────
 */
class DonationOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can('view_any_donation') ?? false;
    }

    protected function getStats(): array
    {
        return Cache::remember('donation_overview_stats', 300, function () {
            $todayCompleted = Donation::completed()
                ->whereDate('created_at', today())
                ->sum('amount');

            $pendingCount = Donation::whereIn('status', ['pending', 'under_review'])->count();

            return [
                Stat::make(__('filament.widgets.donation_overview.total_donations'), number_format(Donation::completed()->sum('amount'), 2).' $')
                    ->description(__('filament.widgets.donation_overview.since_start'))
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('success'),
                Stat::make(__('filament.widgets.donation_overview.today_donations'), number_format($todayCompleted, 2).' $')
                    ->description(__('filament.widgets.donation_overview.today_label'))
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('info'),
                Stat::make(__('filament.widgets.donation_overview.total_donors'), Donation::completed()->distinct('email')->count('email'))
                    ->description(__('filament.widgets.donation_overview.donors_count'))
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('info'),
                Stat::make(__('filament.widgets.donation_overview.pending_reviews'), $pendingCount)
                    ->description(__('filament.widgets.donation_overview.pending_label'))
                    ->descriptionIcon('heroicon-m-clock')
                    ->color($pendingCount > 0 ? 'warning' : 'success'),
                Stat::make(__('filament.widgets.donation_overview.total_users'), User::count())
                    ->description(__('filament.widgets.donation_overview.users_count'))
                    ->descriptionIcon('heroicon-m-users')
                    ->color('warning'),
                Stat::make(__('filament.widgets.donation_overview.total_volunteers'), Volunteer::count())
                    ->description(__('filament.widgets.donation_overview.volunteers_count'))
                    ->descriptionIcon('heroicon-m-hand-raised')
                    ->color('danger'),
            ];
        });
    }
}
