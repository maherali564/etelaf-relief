<section class="projects section" id="projects">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ __('common.main_projects') }}</h2>
            <a href="{{ route('projects.index', ['locale' => $currentLocale]) }}" class="link-more">{{ __('common.view_all') }} <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }}"></i></a>
        </div>
        <div class="projects__grid">
            @foreach($projects as $project)
            <article class="project-card">
                @if($project->first_image)
                <div class="project-card__image" style="background-image: url('{{ asset('storage/'.$project->first_image) }}')"></div>
                @else
                <div class="project-card__image project-card__image--placeholder"></div>
                @endif
                <div class="project-card__body">
                    <h3>{{ trans_field($project, 'title') }}</h3>
                    <p>{{ trans_field($project, 'description') }}</p>
                    @if($project->goal_amount > 0 || ($project->raised_amount ?? 0) > 0)
                    <div class="project-progress">
                        <div class="progress-bar">
                            <div class="progress-bar__fill" style="width: {{ $project->progressPercent() }}%"></div>
                        </div>
                        <div class="progress-stats">
                            <span>{{ __('common.raised') }}: <strong><span data-amount="{{ $project->raised_amount }}">${{ number_format($project->raised_amount, 0) }}</span></strong></span>
                            <span>{{ __('common.goal') }}: <strong><span data-amount="{{ $project->goal_amount }}">${{ number_format($project->goal_amount, 0) }}</span></strong></span>
                            <span><strong>{{ $project->progressPercent() }}%</strong></span>
                        </div>
                    </div>
                    @endif
                    <div class="project-card__actions">
                        <a href="{{ route('donate.project', ['locale' => $currentLocale, 'slug' => $project->slug]) }}" class="btn btn--primary">{{ __('common.contribute') }}</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

<section class="news section" id="news">
    <div class="container">
        <div class="news__columns">
            <div class="news__column">
                <div class="section-header section-header--row">
                    <h2 class="section-title">{{ __('common.announcements') }}</h2>
                    <a href="{{ route('announcements.index', ['locale' => $currentLocale]) }}" class="link-more">{{ __('common.view_all') }}</a>
                </div>
                @foreach($announcements as $post)
                <article class="news-card">
                    <div class="news-card__date">
                        <span class="news-card__day">{{ $post->published_at?->format('d') }}</span>
                        <span class="news-card__month">{{ $post->published_at?->translatedFormat('M') }}</span>
                    </div>
                    <div class="news-card__body">
                        <p><a href="{{ route('posts.show', ['locale' => $currentLocale, 'slug' => $post->slug]) }}">{{ trans_field($post, 'title') }}</a></p>
                        @if($post->campaign && $post->campaign->goal_amount > 0)
                        <div class="news-card__campaign">
                            <small>{{ __('common.raised') }}: <span data-amount="{{ $post->campaign->raised_amount }}">${{ number_format($post->campaign->raised_amount, 0) }}</span> / <span data-amount="{{ $post->campaign->goal_amount }}">${{ number_format($post->campaign->goal_amount, 0) }}</span></small>
                        </div>
                        @endif
                        <a href="{{ route('donate.post', ['locale' => $currentLocale, 'slug' => $post->slug]) }}" class="btn btn--primary btn--sm" style="margin-top:8px">{{ __('common.contribute') }}</a>
                    </div>
                </article>
                @endforeach
                @if($announcements->isEmpty())
                <p class="text-muted">{{ __('common.no_results') }}</p>
                @endif
            </div>
            <div class="news__column">
                <div class="section-header section-header--row">
                    <h2 class="section-title">{{ __('common.success_stories') }}</h2>
                    <a href="{{ route('success-stories.index', ['locale' => $currentLocale]) }}" class="link-more">{{ __('common.view_all') }}</a>
                </div>
                @foreach($successStories as $post)
                <article class="news-card">
                    <div class="news-card__date">
                        <span class="news-card__day">{{ $post->published_at?->format('d') }}</span>
                        <span class="news-card__month">{{ $post->published_at?->translatedFormat('M') }}</span>
                    </div>
                    <div class="news-card__body">
                        <p><a href="{{ route('posts.show', ['locale' => $currentLocale, 'slug' => $post->slug]) }}">{{ trans_field($post, 'title') }}</a></p>
                        @if($post->campaign && $post->campaign->goal_amount > 0)
                        <div class="news-card__campaign">
                            <small>{{ __('common.raised') }}: <span data-amount="{{ $post->campaign->raised_amount }}">${{ number_format($post->campaign->raised_amount, 0) }}</span> / <span data-amount="{{ $post->campaign->goal_amount }}">${{ number_format($post->campaign->goal_amount, 0) }}</span></small>
                        </div>
                        @endif
                        <a href="{{ route('donate.post', ['locale' => $currentLocale, 'slug' => $post->slug]) }}" class="btn btn--primary btn--sm" style="margin-top:8px">{{ __('common.contribute') }}</a>
                    </div>
                </article>
                @endforeach
                @if($successStories->isEmpty())
                <p class="text-muted">{{ __('common.no_results') }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

@if($stories->isNotEmpty())
<section class="stories section">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title">{{ __('common.nav_stories') }}</h2>
            <p>{{ __('home.voices_waiting_desc') }}</p>
        </div>
        <div class="stories__grid">
            @foreach($stories as $story)
            <article class="story-card">
                @if($story->first_image)
                <div class="story-card__image" style="background-image: url('{{ asset('storage/'.$story->first_image) }}')"></div>
                @endif
                <div class="story-card__body">
                    <h3>{{ trans_field($story, 'title') }}</h3>
                    <p>{{ $story->person_name }}{{ $story->age ? ', '.$story->age.' '.__('common.age') : '' }}</p>
                    @if($story->goal_amount > 0 || ($story->raised_amount ?? 0) > 0)
                    <div class="project-progress" style="margin:8px 0">
                        <div class="progress-bar" style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                            <div class="progress-bar__fill" style="width:{{ $story->progressPercent() }}%;height:100%;background:linear-gradient(135deg,#059669,#10b981);border-radius:3px;transition:width 0.5s"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:11px;margin-top:3px;color:#64748b">
                            <span><span data-amount="{{ $story->raised_amount ?? 0 }}">${{ number_format($story->raised_amount ?? 0,0) }}</span> {{ __('common.raised') }}</span>
                            <span><span data-amount="{{ $story->goal_amount }}">${{ number_format($story->goal_amount,0) }}</span> {{ __('common.goal') }}</span>
                        </div>
                    </div>
                    @endif
                    <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
                        <a href="{{ route('donate.story', ['locale' => $currentLocale, 'id' => $story->id]) }}" class="btn btn--primary btn--sm">{{ __('common.contribute') }}</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="programs section">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title">{{ __('common.programs_title') }}</h2>
            <p>{{ __('home.our_programs_desc') }}</p>
        </div>
        <div class="programs__grid">
            @foreach($programs as $program)
            <article class="program-card">
                <div class="program-card__icon">{{ $program->icon }}</div>
                <h3>{{ trans_field($program, 'title') }}</h3>
                <p>{{ trans_field($program, 'description') }}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>
