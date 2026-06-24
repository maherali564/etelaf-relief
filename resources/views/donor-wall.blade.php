@extends('layouts.app')
@section('content')
<section class="section page-header">
    <div class="container">
        <h1 class="section-title">{{ __('donor_wall.page_title') }}</h1>
        <p>{{ __('donor_wall.page_description') }}</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="stats__grid" style="margin-bottom:2rem">
            <div class="stat-item">
                <span class="stat-item__number" data-amount="{{ $totalRaised }}">${{ number_format($totalRaised, 0) }}</span>
                <span class="stat-item__label">{{ __('common.total_raised') }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-item__number">{{ $totalDonors }}</span>
                <span class="stat-item__label">{{ __('common.total_donors') }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-item__number">{{ $donations->total() }}</span>
                <span class="stat-item__label">{{ __('common.total_donations') }}</span>
            </div>
        </div>

        <div class="donor-wall__list">
            @forelse($donations as $donation)
            <div class="donor-wall__item">
                <div class="donor-wall__avatar" style="background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark))">
                    {{ strtoupper(substr($donation->is_anonymous ? __('common.anonymous') : $donation->donor_name, 0, 1)) }}
                </div>
                <div class="donor-wall__info">
                    <strong>{{ $donation->is_anonymous ? __('common.anonymous') : $donation->donor_name }}</strong>
                    <span>{{ $donation->donated_at?->diffForHumans() ?: $donation->created_at->diffForHumans() }}</span>
                    @if($donation->project)<small>{{ trans_field($donation->project, 'title') }}</small>@endif
                    @if($donation->story)<small>{{ trans_field($donation->story, 'title') }}</small>@endif
                </div>
                <span class="donor-wall__amount" data-amount="{{ $donation->amount }}">${{ number_format($donation->amount, 0) }}</span>
            </div>
            @empty
            <p class="donor-wall__empty">{{ __('donor_wall.no_donations') }}</p>
            @endforelse
        </div>

        <div class="donor-wall__pagination">
            {{ $donations->links() }}
        </div>
    </div>
</section>
<style>
.donor-wall__list { display:flex; flex-direction:column; gap:8px; max-width:700px; margin:0 auto; }
.donor-wall__item { display:flex; align-items:center; gap:12px; padding:12px 16px; background:var(--color-bg); border:1px solid var(--color-border); border-radius:var(--radius-md); transition:var(--transition); }
.donor-wall__item:hover { box-shadow:var(--shadow-sm); }
.donor-wall__avatar { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:14px; flex-shrink:0; }
.donor-wall__info { flex:1; min-width:0; }
.donor-wall__info strong { display:block; font-size:14px; }
.donor-wall__info span { display:block; font-size:12px; color:var(--color-text-muted); }
.donor-wall__info small { display:block; font-size:11px; color:var(--color-text-muted); opacity:0.7; }
.donor-wall__amount { font-weight:700; font-size:16px; color:var(--color-primary); white-space:nowrap; }
</style>
@endsection
