@extends('layouts.app')
@section('content')
<section class="section page-header">
    <div class="container">
        <span class="section-tag">{{ __('common.transparency') }}</span>
        <h1 class="section-title">{{ __('common.transparency_title') }}</h1>
        <p>{{ __('common.transparency_desc') }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stats__grid" style="margin-bottom:2rem">
            <div class="stat-item" style="background:var(--color-bg);border-radius:var(--radius-md);padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--color-border)">
                <span class="stat-item__number">${{ number_format($totalRaised, 0) }}</span>
                <span class="stat-item__label">{{ __('common.total_raised') }}</span>
            </div>
            <div class="stat-item" style="background:var(--color-bg);border-radius:var(--radius-md);padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--color-border)">
                <span class="stat-item__number">{{ $totalDonations }}</span>
                <span class="stat-item__label">{{ __('common.total_donations') }}</span>
            </div>
            <div class="stat-item" style="background:var(--color-bg);border-radius:var(--radius-md);padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--color-border)">
                <span class="stat-item__number">{{ $totalDonors }}</span>
                <span class="stat-item__label">{{ __('common.total_donors') }}</span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:2rem">
            <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:28px;box-shadow:var(--shadow-sm);border:1px solid var(--color-border)">
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px">{{ __('common.where_donations_go') }}</h3>
                <div style="margin-bottom:12px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:0.9rem">
                        <span>{{ __('common.direct_aid') }}</span>
                        <span style="font-weight:700">{{ 100 - $adminCostRate }}%</span>
                    </div>
                    <div class="progress-bar" style="height:8px;background:var(--color-border);border-radius:4px;overflow:hidden">
                        <div class="progress-bar__fill" style="width:{{ 100 - $adminCostRate }}%;height:100%;background:var(--color-primary);border-radius:4px"></div>
                    </div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:0.9rem">
                        <span>{{ __('common.admin_costs') }}</span>
                        <span style="font-weight:700">{{ $adminCostRate }}%</span>
                    </div>
                    <div class="progress-bar" style="height:8px;background:var(--color-border);border-radius:4px;overflow:hidden">
                        <div class="progress-bar__fill" style="width:{{ $adminCostRate }}%;height:100%;background:var(--color-accent);border-radius:4px"></div>
                    </div>
                </div>
                <p style="margin-top:16px;font-size:0.85rem;color:var(--color-text-muted)">{{ __('common.admin_cost_desc') }}</p>
            </div>

            <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:28px;box-shadow:var(--shadow-sm);border:1px solid var(--color-border)">
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px">{{ __('common.program_breakdown') }}</h3>
                @forelse($projectBreakdown as $project)
                <div style="margin-bottom:10px">
                    <div style="display:flex;justify-content:space-between;font-size:0.85rem">
                        <span>{{ $project['title'] ?? '' }}</span>
                        <span style="font-weight:600">${{ number_format($project['raised'], 0) }} / ${{ number_format($project['goal'], 0) }}</span>
                    </div>
                    <div class="progress-bar" style="height:6px;background:var(--color-border);border-radius:3px;overflow:hidden">
                        <div class="progress-bar__fill" style="width:{{ $project['percent'] }}%;height:100%;background:var(--color-primary);border-radius:3px"></div>
                    </div>
                </div>
                @empty
                <p style="color:var(--color-text-muted)">{{ __('common.no_projects_yet') }}</p>
                @endforelse
            </div>
        </div>

        <div style="text-align:center;padding:32px;background:var(--color-bg-alt);border-radius:var(--radius-md)">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px">{{ __('common.report_request') }}</h3>
            <p style="color:var(--color-text-muted);font-size:0.9rem;margin-bottom:16px">{{ __('common.report_request_desc') }}</p>
            <a href="{{ route('home', ['locale' => $currentLocale]) }}#contact" class="btn btn--primary">{{ __('common.contact_us') }}</a>
        </div>
    </div>
</section>
@endsection
