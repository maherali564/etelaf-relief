
@if($stories->isNotEmpty())
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag"><i class="fas fa-book-open"></i> {{ __('common.nav_stories') }}</span>
            <h2 class="section-title">{{ __('common.nav_stories') }}</h2>
            <p class="section-desc">{{ __('home.voices_waiting_desc') }}</p>
        </div>
        <div class="stories__grid {{ $stories->count() === 1 ? 'stories__grid--single' : '' }}">
            @foreach($stories as $story)
            <article class="story-card card-hover">
                <div class="story-card__image" style="background-image: url('{{ asset('storage/'.$story->first_image) }}')"></div>
                <div class="story-card__body">
                    <h3>{{ trans_field($story, 'title') }}</h3>
                    <p class="story-card__meta">{{ trans_field($story, 'person_name') }}{{ trans_field($story, 'age') ? ', '.trans_field($story, 'age').' '.__('common.age') : '' }}{{ trans_field($story, 'location') ? ' | '.trans_field($story, 'location') : '' }}</p>
                    @if($story->goal_amount > 0 || ($story->raised_amount ?? 0) > 0)
                    <div class="project-card__progress">
                        <div class="project-card__bar">
                            <div class="project-card__fill" style="width:{{ $story->progressPercent() }}%"></div>
                        </div>
                        <div class="project-card__stats">
                            <span>{{ number_format($story->raised_amount ?? 0) }} / {{ number_format($story->goal_amount) }}</span>
                            <span>{{ $story->progressPercent() }}%</span>
                        </div>
                    </div>
                    @endif
                    <div class="story-card__actions">
                        <a href="{{ route('stories.show', ['locale' => $currentLocale, 'id' => $story->id]) }}" class="btn btn--primary btn--sm">{{ __('common.contribute') }}</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif
