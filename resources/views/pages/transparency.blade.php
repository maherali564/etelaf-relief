@extends('layouts.app')
@section('content')

<section class="hero-detailed">
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-shield-halved"></i> {{ __('common.transparency') }}</span>
        <h1 class="hero-detailed__title">{{ __('common.transparency_title') }}</h1>
        <p class="hero-detailed__desc">{{ __('common.transparency_desc') }}</p>
    </div>
</section>

<section class="section section--muted">
    <div class="container">
        <div class="stats-exec__grid-cards">
            <div class="stat-exec-card animate-fadeInUp">
                <div class="stat-exec-card__glow"></div>
                <div class="stat-exec-card__inner">
                    <div class="stat-exec-card__icon"><i class="fas fa-hand-holding-heart"></i></div>
                    <div class="stat-exec-card__value">${{ number_format($totalRaised, 0) }}</div>
                    <div class="stat-exec-card__bar"></div>
                    <div class="stat-exec-card__label">{{ __('common.total_raised') }}</div>
                </div>
            </div>
            <div class="stat-exec-card animate-fadeInUp">
                <div class="stat-exec-card__glow"></div>
                <div class="stat-exec-card__inner">
                    <div class="stat-exec-card__icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-exec-card__value">{{ $totalDonations }}</div>
                    <div class="stat-exec-card__bar"></div>
                    <div class="stat-exec-card__label">{{ __('common.total_donations') }}</div>
                </div>
            </div>
            <div class="stat-exec-card animate-fadeInUp">
                <div class="stat-exec-card__glow"></div>
                <div class="stat-exec-card__inner">
                    <div class="stat-exec-card__icon"><i class="fas fa-users"></i></div>
                    <div class="stat-exec-card__value">{{ $totalDonors }}</div>
                    <div class="stat-exec-card__bar"></div>
                    <div class="stat-exec-card__label">{{ __('common.total_donors') }}</div>
                </div>
            </div>
        </div>

        <div class="stats-exec__grid-cards" style="margin-top:40px">
            <div class="stat-exec-card animate-fadeInUp">
                <div class="stat-exec-card__glow"></div>
                <div class="stat-exec-card__inner">
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;color:#fff;text-align:center">{{ __('common.where_donations_go') }}</h3>
                    <div style="margin-bottom:14px">
                        <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:#a1a1aa;margin-bottom:4px">
                            <span>{{ __('common.direct_aid') }}</span>
                            <span style="font-weight:700;color:#34d399">{{ 100 - $adminCostRate }}%</span>
                        </div>
                        <div style="height:8px;background:rgba(255,255,255,.1);border-radius:4px;overflow:hidden">
                            <div style="width:{{ 100 - $adminCostRate }}%;height:100%;background:linear-gradient(90deg,#059669,#34d399);border-radius:4px"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:#a1a1aa;margin-bottom:4px">
                            <span>{{ __('common.admin_costs') }}</span>
                            <span style="font-weight:700;color:#fbbf24">{{ $adminCostRate }}%</span>
                        </div>
                        <div style="height:8px;background:rgba(255,255,255,.1);border-radius:4px;overflow:hidden">
                            <div style="width:{{ $adminCostRate }}%;height:100%;background:linear-gradient(90deg,#d97706,#fbbf24);border-radius:4px"></div>
                        </div>
                    </div>
                    <p style="margin-top:12px;font-size:0.8rem;color:#71717a;text-align:center">{{ __('common.admin_cost_desc') }}</p>
                </div>
            </div>

            <div class="stat-exec-card animate-fadeInUp">
                <div class="stat-exec-card__glow"></div>
                <div class="stat-exec-card__inner">
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;color:#fff;text-align:center">{{ __('common.program_breakdown') }}</h3>
                    @forelse($projectBreakdown as $project)
                    <div style="margin-bottom:10px">
                        <div style="display:flex;justify-content:space-between;font-size:0.82rem;color:#a1a1aa;margin-bottom:3px">
                            <span>{{ $project['title'] ?? '' }}</span>
                            <span style="font-weight:600;color:#fff">${{ number_format($project['raised'], 0) }} / ${{ number_format($project['goal'], 0) }}</span>
                        </div>
                        <div style="height:6px;background:rgba(255,255,255,.1);border-radius:3px;overflow:hidden">
                            <div style="width:{{ $project['percent'] }}%;height:100%;background:linear-gradient(90deg,#059669,#34d399);border-radius:3px"></div>
                        </div>
                    </div>
                    @empty
                    <p style="color:#71717a;text-align:center;font-size:0.85rem">{{ __('common.no_projects_yet') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div style="text-align:center;padding:40px 32px;margin-top:40px;background:linear-gradient(135deg,#059669,#10b981);border-radius:16px">
            <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:8px;color:#fff">{{ __('common.report_request') }}</h3>
            <p style="color:rgba(255,255,255,.8);font-size:0.9rem;margin-bottom:20px">{{ __('common.report_request_desc') }}</p>
            <a href="{{ route('home', ['locale' => $currentLocale]) }}#contact" class="btn btn--outline" style="color:#fff;border-color:rgba(255,255,255,.5);background:transparent">{{ __('common.contact_us') }}</a>
        </div>
    </div>
</section>

<style>
.section--muted { background: #0f172a; }
.section--muted .container { padding-top: 40px; padding-bottom: 40px; }
</style>
@endsection