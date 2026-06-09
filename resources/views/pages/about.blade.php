@extends('layouts.app')
@section('content')
<section class="section page-header">
    <div class="container">
        <span class="section-tag">{{ __('common.about_us') }}</span>
        <h1 class="section-title">{{ __('site.about_title') }}</h1>
        <p>{{ __('site.about_desc') }}</p>
    </div>
</section>

{{-- Our Story --}}
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center">
            <div>
                <span class="section-tag" style="margin-bottom:8px;display:inline-block">{{ __('site.our_story') }}</span>
                <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:16px;line-height:1.3">{{ __('site.story_title') }}</h2>
                <div style="color:var(--color-text-muted);line-height:1.8;font-size:1rem">
                    @foreach(['story_p1', 'story_p2', 'story_p3'] as $key)
                    <p style="margin-bottom:12px">{{ __("site.$key") }}</p>
                    @endforeach
                </div>
            </div>
            <div style="height:400px;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;color:#fff;font-size:4rem">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
        </div>
    </div>
</section>

{{-- Mission & Vision --}}
<section style="background:var(--color-bg-alt);padding:80px 0">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:32px;box-shadow:var(--shadow-sm);border:1px solid var(--color-border);text-align:center">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 16px"><i class="fas fa-bullseye"></i></div>
                <h3 style="font-size:1.3rem;font-weight:700;margin-bottom:8px">{{ __('site.mission_title') }}</h3>
                <p style="color:var(--color-text-muted);line-height:1.7">{{ __('site.mission_desc') }}</p>
            </div>
            <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:32px;box-shadow:var(--shadow-sm);border:1px solid var(--color-border);text-align:center">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--color-accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 16px"><i class="fas fa-eye"></i></div>
                <h3 style="font-size:1.3rem;font-weight:700;margin-bottom:8px">{{ __('site.vision_title') }}</h3>
                <p style="color:var(--color-text-muted);line-height:1.7">{{ __('site.vision_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Core Values --}}
<section class="section">
    <div class="container">
        <div class="section-header section-header--center">
            <span class="section-tag">{{ __('site.core_values') }}</span>
            <h2 class="section-title">{{ __('site.values_title') }}</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px">
            @foreach(['transparency', 'integrity', 'impact', 'compassion'] as $val)
            <div style="text-align:center;padding:28px;background:var(--color-bg);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);border:1px solid var(--color-border)">
                <div style="width:56px;height:56px;border-radius:50%;background:var(--color-bg-alt);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.3rem;color:var(--color-primary)">
                    <i class="fas fa-{{ ['eye','shield-alt','chart-line','heart'][$loop->index] }}"></i>
                </div>
                <h4 style="font-weight:700;margin-bottom:6px">{{ __("site.value_{$val}_title") }}</h4>
                <p style="font-size:0.85rem;color:var(--color-text-muted)">{{ __("site.value_{$val}_desc") }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Impact Stats --}}
<section style="background:var(--color-bg-dark);padding:60px 0">
    <div class="container">
        <div class="stats__grid">
            <div class="stat-item stat-item--dark">
                <span class="stat-item__number" data-amount="{{ $totalRaised }}">${{ number_format($totalRaised, 0) }}</span>
                <span class="stat-item__label">{{ __('common.total_raised') }}</span>
            </div>
            <div class="stat-item stat-item--dark">
                <span class="stat-item__number">{{ $totalDonations }}</span>
                <span class="stat-item__label">{{ __('common.total_donations') }}</span>
            </div>
            <div class="stat-item stat-item--dark">
                <span class="stat-item__number">{{ $totalDonors }}</span>
                <span class="stat-item__label">{{ __('common.total_donors') }}</span>
            </div>
            @foreach($achievementStats as $stat)
            <div class="stat-item stat-item--dark">
                <span class="stat-item__number">{{ number_format($stat->value) }}</span>
                <span class="stat-item__label">{{ trans_field($stat, 'label') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Partners --}}
<section class="section">
    <div class="container">
        <div class="section-header section-header--center">
            <span class="section-tag">{{ __('site.our_partners') }}</span>
            <h2 class="section-title">{{ __('site.partners_title') }}</h2>
            <p>{{ __('site.partners_desc') }}</p>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:24px;justify-content:center">
            @for($i=0;$i<6;$i++)
            <div style="width:160px;height:100px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;padding:16px;box-shadow:var(--shadow-sm);font-size:0.85rem;color:var(--color-text-muted);text-align:center">
                {{ __('site.partner_placeholder') }}
            </div>
            @endfor
        </div>
    </div>
</section>

{{-- CTA --}}
<section style="background:var(--color-primary);padding:60px 0;text-align:center;color:#fff">
    <div class="container">
        <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:12px">{{ __('site.cta_title') }}</h2>
        <p style="opacity:0.9;font-size:1.05rem;margin-bottom:24px;max-width:600px;margin-left:auto;margin-right:auto">{{ __('site.cta_desc') }}</p>
        <a href="{{ route('home', ['locale' => $currentLocale]) }}#donate" class="btn" style="background:#fff;color:var(--color-primary);font-weight:700;padding:14px 36px">{{ __('common.donate_now') }}</a>
    </div>
</section>
@endsection
