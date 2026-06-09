<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DonationChart;
use App\Filament\Widgets\DonationOverview;
use App\Filament\Widgets\LatestDonations;
use App\Http\Middleware\SetLocale;
use App\Models\ChatSession;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(env('FILAMENT_PATH', 'admin'))
            ->login()
            ->brandName(__('filament.brand'))
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::hex('#0d6b4f'),
                'danger' => Color::Rose,
                'gray' => Color::Slate,
            ])
            ->font('Cairo')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                __('filament.nav.groups.content'),
                __('filament.nav.groups.pages_projects'),
                __('filament.nav.groups.campaigns_donations'),
                __('filament.nav.groups.users'),
                __('filament.nav.groups.messages'),
                __('filament.nav.groups.settings'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                DonationOverview::class,
                DonationChart::class,
                LatestDonations::class,
            ])
            ->navigationItems([
                NavigationItem::make(__('filament.chat.navigation_label'))
                    ->url('/admin/chats')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->group(__('filament.nav.groups.messages'))
                    ->sort(3)
                    ->badge(fn () => ChatSession::waiting()->count())
                    ->isActiveWhen(fn () => request()->is('admin/chats*')),
                NavigationItem::make('Pulse')
                    ->url('/pulse', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-heart')
                    ->group(__('filament.nav.groups.settings'))
                    ->sort(99)
                    ->visible(fn () => auth()->user()?->hasRole('super_admin')),
            ])
            ->userMenuItems([
                MenuItem::make()->label('English')->icon('heroicon-o-language')
                    ->url(fn () => route('admin.locale', 'en'))->sort(-5),
                MenuItem::make()->label('العربية')->icon('heroicon-o-language')
                    ->url(fn () => route('admin.locale', 'ar'))->sort(-4),
                MenuItem::make()->label('Español')->icon('heroicon-o-language')
                    ->url(fn () => route('admin.locale', 'es'))->sort(-3),
                MenuItem::make()->label('Türkçe')->icon('heroicon-o-language')
                    ->url(fn () => route('admin.locale', 'tr'))->sort(-2),
                MenuItem::make()->label('Bahasa Indonesia')->icon('heroicon-o-language')
                    ->url(fn () => route('admin.locale', 'id'))->sort(-1),
                MenuItem::make()->label('Svenska')->icon('heroicon-o-language')
                    ->url(fn () => route('admin.locale', 'sv'))->sort(0),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
