@extends('layouts.app')
@php
    $allImages = $post->images ?? [];
    if ($post->image && !in_array($post->image, $allImages)) {
        array_unshift($allImages, $post->image);
    }
@endphp
@section('content')
<section class="section">
    <div class="container donate-post">
        <div class="donate-project__grid">
            <div class="donate-project__info">
                @if(count($allImages) > 0)
                <div class="donate-project__image">
                    <img loading="lazy" src="{{ asset('storage/'.$allImages[0]) }}" alt="{{ trans_field($post, 'title') }}">
                </div>
                @endif
                <h1 class="section-title">{{ trans_field($post, 'title') }}</h1>
                <p style="color:var(--color-text-muted);margin-bottom:1.5rem">{{ $post->published_at?->format('Y-m-d') }}</p>
                <div class="donate-project__description">{!! trans_field($post, 'content') ?: nl2br(e(trans_field($post, 'excerpt'))) !!}</div>

                @if($post->campaign && $post->campaign->goal_amount > 0)
                <div class="donate-project__progress">
                    <h4 style="margin-bottom:0.75rem;color:var(--color-primary)">{{ trans_field($post->campaign, 'title') }}</h4>
                    <div class="progress-bar">
                        <div class="progress-bar__fill" style="width:{{ $post->campaign->progressPercent() }}%"></div>
                    </div>
                    <div class="progress-stats">
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">{{ __('common.raised') }}</span>
                            <span class="progress-stats__value">${{ number_format($post->campaign->raised_amount ?? 0) }}</span>
                        </div>
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">{{ __('common.goal') }}</span>
                            <span class="progress-stats__value">${{ number_format($post->campaign->goal_amount) }}</span>
                        </div>
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">%</span>
                            <span class="progress-stats__value">{{ $post->campaign->progressPercent() }}%</span>
                        </div>
                    </div>
                </div>
                @endif

                @include('partials.donate-donors')
            </div>

            <div class="donate-project__form">
                <div class="donate-form-card">
                    <h3>{{ __('donate.page_title') }}</h3>
                    <form action="{{ route('donate.store', ['locale' => $currentLocale]) }}" method="POST" class="donate-form {{ $isRtl ? 'donate-form--rtl' : 'donate-form--ltr' }}">
                        <input type="hidden" name="post_id" value="{{ $post->id }}">
                        @if($post->campaign_id)
                        <input type="hidden" name="campaign_id" value="{{ $post->campaign_id }}">
                        @endif
                        @section('donate_entity_fields')
                        <div class="form-group">
                            <label>{{ __('donate.select_project') }}</label>
                            <select name="project_id" id="projectSelect">
                                <option value="">{{ __('donate.main_campaign') }}</option>
                                @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ trans_field($proj, 'title') }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endsection
                        @section('donate_entity_js')
                        @endsection
                        @include('partials.donate-form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
