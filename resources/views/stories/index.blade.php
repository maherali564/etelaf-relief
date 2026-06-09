@extends('layouts.app')
@section('content')
<section class="section page-header">
    <div class="container">
        <h1 class="section-title">{{ __('common.nav_stories') }}</h1>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="stories__grid {{ $stories->count() === 1 ? 'stories__grid--single' : '' }}">
            @forelse($stories as $story)
            <article class="story-card">
                @if($story->first_image)
                <div class="story-card__image" style="background-image: url('{{ asset('storage/'.$story->first_image) }}')"></div>
                @endif
                <div class="story-card__body">
                    <h3>{{ trans_field($story, 'title') }}</h3>
                    <p class="story-card__meta">{{ $story->person_name }}{{ $story->age ? ', '.$story->age.' '.__('common.age') : '' }}{{ $story->location ? ' | '.$story->location : '' }}</p>
                    @if($story->goal_amount > 0 || ($story->raised_amount ?? 0) > 0)
                    <div class="project-progress" style="margin:8px 0">
                        <div class="progress-bar" style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                            <div class="progress-bar__fill" style="width:{{ $story->progressPercent() }}%;height:100%;background:var(--color-primary, #2563eb);border-radius:3px;transition:width 0.5s"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:11px;margin-top:3px;color:#64748b">
                            <span>${{ number_format($story->raised_amount ?? 0,0) }} {{ __('common.raised') }}</span>
                            <span>${{ number_format($story->goal_amount,0) }} {{ __('common.goal') }}</span>
                        </div>
                    </div>
                    @endif
                    <div class="story-card__actions" style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
                        <a href="{{ route('donate.story', ['locale' => $currentLocale, 'id' => $story->id]) }}" class="btn btn--primary btn--sm">{{ __('common.contribute') }}</a>
                    </div>
                </div>
            </article>
            @empty
            <p>{{ __('common.no_results') }}</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
