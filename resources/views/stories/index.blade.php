@extends('layouts.app')
@section('content')
<section class="hero-detailed">
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-book-open"></i> {{ __('home.stories_tag') ?? __('common.nav_stories') }}</span>
        <h1 class="hero-detailed__title">{{ __('common.nav_stories') }}</h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stories__grid {{ $stories->count() === 1 ? 'stories__grid--single' : '' }}">
            @forelse($stories as $story)
            <article class="story-card card-hover">
                @if($story->first_image)
                <div class="story-card__image" style="background-image: url('{{ asset('storage/'.$story->first_image) }}')"></div>
                @endif
                <div class="story-card__body">
                    <h3>{{ trans_field($story, 'title') }}</h3>
                    <p class="story-card__meta">{{ trans_field($story, 'person_name') }}{{ trans_field($story, 'age') ? ', '.trans_field($story, 'age').' '.__('common.age') : '' }}{{ trans_field($story, 'location') ? ' | '.trans_field($story, 'location') : '' }}</p>
                    <x-progress-bar :raised="$story->raised_amount ?? 0" :goal="$story->goal_amount" :label="true" />
                    <div class="story-card__actions">
                        <a href="{{ route('stories.show', ['locale' => $currentLocale, 'id' => $story->id]) }}" class="btn btn--emerald btn--sm">{{ __('common.details') }}</a>
                        <a href="{{ route('stories.show', ['locale' => $currentLocale, 'id' => $story->id]) }}" class="btn btn--primary btn--sm">{{ __('common.contribute') }}</a>
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
