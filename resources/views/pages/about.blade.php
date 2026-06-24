@extends('layouts.app')
@section('content')

<section class="section page-header">
    <div class="container">
        <span class="section-tag"><i class="fas fa-info-circle"></i> {{ __('common.about_us') }}</span>
        <h1 class="section-title">{{ __('site.about_title') }}</h1>
        <p>{{ __('site.about_desc') }}</p>
    </div>
</section>

<div class="about-page pb-24">
    {{-- Our Story --}}
    <section class="section">
        <div class="container">
            <div class="about-story-grid">
                <div class="about-story-content">
                    <span class="section-tag"><i class="fas fa-book-open"></i> {{ __('site.our_story') }}</span>
                    <h2 class="about-story-title">{{ __('site.story_title') }}</h2>

                    @if(!empty($settings->about_content))
                    <div class="about-story-quote">
                        <p>{{ $settings->about_content }}</p>
                    </div>
                    @endif

                    <div class="about-story-text">
                        <p>{{ __('site.story_p1') }}</p>
                        @if(__('site.story_p2') && __('site.story_p2') !== 'site.story_p2')
                        <p>{{ __('site.story_p2') }}</p>
                        @endif
                    </div>

                    @if(count($aboutFeatures) > 0)
                    <div class="about-features-grid">
                        @foreach($aboutFeatures as $feature)
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span>{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="about-features-grid">
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span>{{ __('site.value_transparency_title') }}</span>
                        </div>
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span>{{ __('site.value_integrity_title') }}</span>
                        </div>
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span>{{ __('site.value_impact_title') }}</span>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="about-story-visual">
                    <div class="about-story-visual__glow"></div>
                    <div class="about-story-visual__frame">
                        <img
                            src="{{ $settings->about_image ? Storage::url($settings->about_image) : asset('images/about-hero.webp') }}"
                            alt="{{ __('common.about_us') }}"
                            class="about-story-visual__img"
                        />
                        <div class="about-story-visual__badge">
                            <div class="about-story-visual__badge-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="about-story-visual__badge-text">
                                <h4>{{ app()->getLocale() === 'ar' ? 'عطاء ممتد ومستمر' : 'Continuous Giving' }}</h4>
                                <p>{{ app()->getLocale() === 'ar' ? 'مشاريعنا تخدم الإنسان أينما كان' : 'Our projects serve humanity globally' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Mission & Vision --}}
    <section class="section">
        <div class="container">
            <div class="about-mv-grid">
                <div class="about-mv-card about-mv-card--mission">
                    <div class="about-mv-card__glow"></div>
                    <div class="about-mv-card__body">
                        <div class="about-mv-card__icon about-mv-card__icon--emerald">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>{{ __('site.mission_title') }}</h3>
                        <p>{{ __('site.mission_desc') }}</p>
                    </div>
                </div>
                <div class="about-mv-card about-mv-card--vision">
                    <div class="about-mv-card__glow"></div>
                    <div class="about-mv-card__body">
                        <div class="about-mv-card__icon about-mv-card__icon--teal">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>{{ __('site.vision_title') }}</h3>
                        <p>{{ __('site.vision_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Core Values --}}
    <section class="section">
        <div class="container">
            <div class="section-header section-header--center">
                <span class="section-tag"><i class="fas fa-star"></i> {{ __('site.core_values') }}</span>
                <h2 class="section-title">{{ __('site.values_title') }}</h2>
            </div>
            <div class="about-values-grid">
                @php
                    $values = [
                        'transparency' => ['icon' => 'fa-shield-halved', 'color' => 'emerald'],
                        'integrity' => ['icon' => 'fa-bolt', 'color' => 'teal'],
                        'impact' => ['icon' => 'fa-hand-holding-heart', 'color' => 'red'],
                        'compassion' => ['icon' => 'fa-seedling', 'color' => 'indigo'],
                    ];
                @endphp
                @foreach($values as $key => $val)
                <div class="about-value-card card-hover">
                    <div class="about-value-card__icon about-value-card__icon--{{ $val['color'] }}">
                        <i class="fas {{ $val['icon'] }}"></i>
                    </div>
                    <h4>{{ __("site.value_{$key}_title") }}</h4>
                    <p>{{ __("site.value_{$key}_desc") }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="section">
        <div class="container">
            <div class="about-cta">
                <div class="about-cta__glow"></div>
                <div class="about-cta__body">
                    <h3 class="about-cta__title">{{ __('site.cta_title') }}</h3>
                    <p class="about-cta__desc">{{ __('site.cta_desc') }}</p>
                    <div class="about-cta__actions">
                        <a href="{{ route('donate.page', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--lg about-cta__btn about-cta__btn--primary">
                            <span>{{ __('common.donate_now') }}</span>
                            <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} about-cta__arrow"></i>
                        </a>
                        <a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}" class="btn btn--lg about-cta__btn about-cta__btn--outline">
                            <i class="fas fa-hands-helping"></i>
                            <span>{{ __('volunteer.nav') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
