@extends('layouts.app')
@php $s = $settings; @endphp
@php
    $dictAr = ['eyebrow'=>__('home.eyebrow'),'title'=>'حين تساهم، يصل أثرك إلى حيث الحاجة أكبر','lead'=>__('home.hero_lead'),'cta_donate'=>__('common.donate_now'),'cta_explore'=>__('home.explore_work')];
    $dictEn = ['eyebrow'=>'','title'=>'When you give, your impact reaches where it\'s needed most','lead'=>'','cta_donate'=>'Donate Now','cta_explore'=>'Explore Our Work'];
@endphp

@section('content')

<div class="overflow-hidden">

<!-- ═══════════ HERO ═══════════ -->
<section class="hero" dir="{{ $currentLocale === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="hero__pattern" aria-hidden="true"></div>
    <div class="hero__inner">
        <div class="hero__content">
            @if($currentLocale === 'ar' && !empty($urgentNote))
            <div class="hero__note"><i class="fas fa-circle" style="font-size:6px;color:var(--gold)"></i> {{ $urgentNote }}</div>
            @endif
            <h1 class="hero__title">{{ $currentLocale === 'ar' ? $dictAr['title'] : $dictEn['title'] }}</h1>
            <p class="hero__desc">{{ $currentLocale === 'ar' ? $dictAr['lead'] : ($heroSubtitle ?? __('home.hero_lead')) }}</p>
            <div class="hero__actions">
                <a href="{{ route('donate.page', ['locale' => $currentLocale]) }}" class="btn btn--primary"><i class="fas fa-heart"></i> {{ $currentLocale === 'ar' ? $dictAr['cta_donate'] : $dictEn['cta_donate'] }}</a>
                <a href="{{ route('projects.index', ['locale' => $currentLocale]) }}" class="btn btn--outline">{{ $currentLocale === 'ar' ? $dictAr['cta_explore'] : $dictEn['cta_explore'] }}</a>
            </div>
        </div>
        <div class="hero__map">
            @include('partials.hero-map')
        </div>
    </div>
</section>

<!-- ═══════════ UNIFIED STATS ═══════════ -->
@php $allStats = isset($statistics['humanitarian']) && $statistics['humanitarian']->isNotEmpty() ? $statistics['humanitarian']->concat($statistics['achievements'] ?? collect()) : ($statistics['achievements'] ?? collect()); @endphp
@if(!empty($allStats))
<section class="stats-exec">
    <div class="stats-exec__grid" aria-hidden="true"><svg width="100%" height="100%"><defs><pattern id="sg" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0L0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100%" height="100%" fill="url(#sg)"/></svg></div>
    <div class="stats-exec__glow1" aria-hidden="true"></div>
    <div class="stats-exec__glow2" aria-hidden="true"></div>
    <div class="stats-exec__accent" aria-hidden="true"></div>
    <div class="stats-exec__inner">
        <div class="section-header">
            <div class="section-tag" style="background:rgba(99,102,241,0.1);border-color:rgba(99,102,241,0.2);color:#818cf8">
                <i class="fas fa-hand-holding-heart"></i> {{ __('home.analytics_tag') }}
            </div>
            <h2 class="section-title" style="color:#fff">{{ __('home.analytics_title') }}</h2>
            <p class="section-desc" style="color:#a1a1aa">{{ __('home.analytics_desc') }}</p>
        </div>
        <div class="stats-exec__grid-cards">
            @foreach($allStats as $stat)
            <div class="stat-exec-card animate-fadeInUp delay-{{ $loop->index % 6 * 100 + 100 }}">
                <div class="stat-exec-card__glow" aria-hidden="true"></div>
                <div class="stat-exec-card__inner">
                    @if(!empty($stat['icon']))<div class="stat-exec-card__icon"><i class="fas fa-{{ $stat['icon'] }}"></i></div>@endif
                    <div class="stat-exec-card__value">@if(!empty($stat['prefix']))<small>{{ $stat['prefix'] }}</small>@endif{{ $stat['value'] }}</div>
                    <div class="stat-exec-card__bar" aria-hidden="true"></div>
                    <div class="stat-exec-card__label">{{ $stat['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ═══════════ PROJECTS (only if data exists) ═══════════ -->
@if(isset($projects) && $projects->isNotEmpty())
<section class="section" id="work">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-hands-helping"></i> {{ __('home.projects_tag') }}</div>
            <h2 class="section-title">{{ __('home.projects_title') }}</h2>
            <p class="section-desc">{{ __('home.projects_desc') }}</p>
        </div>
        <div class="projects__slider">
            <div class="projects__track {{ $projects->count() === 1 ? 'projects__track--single' : '' }}" id="projectsTrack">
                @foreach($projects as $project)
                <div class="project-card card-hover">
                    <div class="project-card__image">
                        <img src="{{ asset('storage/'.($project->image ?? $project->images[0] ?? 'default.jpg')) }}" alt="{{ trans_field($project, 'title') }}" loading="lazy">
                    </div>
                    <div class="project-card__body">
                        <h3>{{ trans_field($project, 'title') }}</h3>
                        <div class="project-card__meta" style="font-size:0.78rem;color:#64748b;margin-bottom:6px;display:flex;gap:10px;flex-wrap:wrap;">
                            <span><i class="fas fa-calendar-alt"></i> {{ $project->created_at ? $project->created_at->format('Y-m-d') : '—' }}</span>
                            <span><i class="fas fa-map-marker-alt"></i> {{ trans_field($project, 'location') ?? __('common.not_specified') }}</span>
                        </div>
                        <p>{{ Str::limit(trans_field($project, 'description') ?? trans_field($project, 'content'), 100) }}</p>
                        @if(($project->goal_amount ?? 0) > 0 || ($project->raised_amount ?? 0) > 0)
                        <div class="project-card__progress">
                            <div class="project-card__bar">
                                <div class="project-card__fill" style="width:{{ $project->progressPercent() }}%"></div>
                            </div>
                            <div class="project-card__stats">
                                <span>{{ number_format($project->raised_amount ?? 0) }} / {{ number_format($project->goal_amount) }}</span>
                                <span>{{ $project->progressPercent() }}%</span>
                            </div>
                        </div>
                        @endif
                        <div class="project-card__actions">
                            <a href="{{ route('projects.show', ['locale' => $currentLocale, 'slug' => $project->slug]) }}" class="btn btn--primary btn--sm">{{ __('common.donate_now') }}</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<!-- ═══════════ PROJECTS GRID ═══════════ -->
@include('partials.home-content')

<!-- ═══════════ VOLUNTEER + CONTACT ═══════════ -->
@include('partials.home-bottom')

</div>

@endsection

@push('head')
<style>
.projects__track--single { justify-content: center; }
.stories__grid--single { display: flex; justify-content: center; }
</style>
@endpush
@push('scripts')
<script>
var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });
document.querySelectorAll('.stat-exec-card, .project-card').forEach(function(el) {
    observer.observe(el);
});
</script>
@endpush
