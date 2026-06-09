@extends('layouts.app')
@section('content')
<section class="section page-header">
    <div class="container">
        <h1 class="section-title">{{ __('site.nav_projects') }}</h1>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="projects__grid {{ $projects->count() === 1 ? 'projects__grid--single' : '' }}">
            @foreach($projects as $project)
            <article class="project-card">
                @if($project->first_image)
                <div class="project-card__image" style="background-image:url('{{ asset('storage/'.$project->first_image) }}')"></div>
                @endif
                <div class="project-card__body">
                    <h3>{{ trans_field($project, 'title') }}</h3>
                    <p>{{ trans_field($project, 'description') }}</p>
                    @if($project->goal_amount > 0 || ($project->raised_amount ?? 0) > 0)
                    <div class="project-progress">
                        <div class="project-progress__bar">
                            <div class="project-progress__fill" style="width:{{ $project->progressPercent() }}%"></div>
                        </div>
                        <div class="project-progress__stats">
                            <span>${{ number_format($project->raised_amount ?? 0) }} {{ __('site.raised') }}</span>
                            <span>${{ number_format($project->goal_amount) }} {{ __('site.goal') }}</span>
                        </div>
                    </div>
                    @endif
                    <div class="project-card__actions">
                        <a href="{{ route('donate.project', ['locale' => $currentLocale, 'slug' => $project->slug]) }}" class="btn btn--primary">{{ __('site.contribute') }}</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
