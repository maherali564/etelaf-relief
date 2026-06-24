@extends('layouts.app')
@section('content')
<section class="hero-detailed">
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-project-diagram"></i> {{ __('home.projects_tag') ?? __('site.nav_projects') }}</span>
        <h1 class="hero-detailed__title">{{ __('site.nav_projects') }}</h1>
        <p class="hero-detailed__desc">{{ __('home.projects_desc') ?? '' }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="projects__slider">
            <div class="projects__track {{ $projects->count() === 1 ? 'projects__track--single' : '' }}" id="projectsTrack">
            @forelse($projects as $project)
            <div class="project-card card-hover">
                <div class="project-card__image">
                    <img src="{{ asset('storage/'.($project->image ?? $project->images[0] ?? $project->first_image ?? 'default.jpg')) }}" alt="{{ trans_field($project, 'title') }}" loading="lazy">
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
                            <div class="project-card__fill" style="width:{{ $project->progressPercent() ?? 0 }}%"></div>
                        </div>
                        <div class="project-card__stats">
                            <span>{{ number_format($project->raised_amount ?? 0) }} / {{ number_format($project->goal_amount) }}</span>
                            <span>{{ $project->progressPercent() ?? 0 }}%</span>
                        </div>
                    </div>
                    @endif
                    <div class="project-card__actions">
                        <a href="{{ route('projects.show', ['locale' => $currentLocale, 'slug' => $project->slug]) }}" class="btn btn--primary btn--sm">{{ __('common.donate_now') }}</a>
                    </div>
                </div>
            </div>
            @empty
            <p>{{ __('common.no_results') }}</p>
            @endforelse
        </div>
    </div>
    </div>
</section>
@endsection
