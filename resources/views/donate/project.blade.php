@extends('layouts.app')
@php
    $allImages = $project->images ?? [];
    if ($project->image && !in_array($project->image, $allImages)) {
        array_unshift($allImages, $project->image);
    }
@endphp
@section('content')
<section class="section">
    <div class="container donate-project">
        <div class="donate-project__grid">
            <div class="donate-project__info">
                @if(count($allImages) > 0)
                <div class="donate-project__image">
                    <img loading="lazy" src="{{ asset('storage/'.$allImages[0]) }}" alt="{{ trans_field($project, 'title') }}">
                </div>
                @endif
                <h1 class="section-title">{{ trans_field($project, 'title') }}</h1>
                <div class="donate-project__description">{!! trans_field($project, 'content') ?: nl2br(e(trans_field($project, 'description'))) !!}</div>

                @if($project->goal_amount > 0)
                <div class="donate-project__progress">
                    <div class="progress-bar">
                        <div class="progress-bar__fill" style="width:{{ $project->progressPercent() }}%"></div>
                    </div>
                    <div class="progress-stats">
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">{{ __('common.raised') }}</span>
                            <span class="progress-stats__value">${{ number_format($project->raised_amount ?? 0) }}</span>
                        </div>
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">{{ __('common.goal') }}</span>
                            <span class="progress-stats__value">${{ number_format($project->goal_amount) }}</span>
                        </div>
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">%</span>
                            <span class="progress-stats__value">{{ $project->progressPercent() }}%</span>
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
                        @section('donate_entity_fields')
                        <div class="form-group">
                            <label>{{ __('donate.select_project') }}</label>
                            <select name="project_id" id="projectSelect">
                                <option value="">{{ $project->title ?: __('donate.main_campaign') }}</option>
                                @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" {{ $proj->id == $project->id ? 'selected' : '' }}>{{ trans_field($proj, 'title') }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endsection
                        @section('donate_entity_js')
                        document.getElementById('projectSelect').addEventListener('change', function() {
                            if (this.value) {
                                window.location.href = '{{ url($currentLocale . "/donate/project") }}/' + this.value;
                            }
                        });
                        @endsection
                        @include('partials.donate-form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
