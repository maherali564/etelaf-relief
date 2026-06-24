@extends('layouts.app')
@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp

@section('title', __('common.programs_title'))
@section('meta_description', __('home.our_programs_desc'))

@section('content')
<section class="page-header">
    <div class="container">
        <span class="section-tag"><i class="fas fa-cubes"></i> {{ __('common.programs_title') }}</span>
        <h1 class="section-title">{{ __('common.programs_title') }}</h1>
        <p>{{ __('home.our_programs_desc') }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if($programs->isNotEmpty())
        <div class="programs__grid">
            @foreach($programs as $program)
            <article class="program-card card-hover">
                <div class="program-card__icon">{!! safeHtml($program->icon) !!}</div>
                <h3>{{ trans_field($program, 'title') }}</h3>
                <p>{{ trans_field($program, 'description') }}</p>
                @if($program->activeItems->isNotEmpty())
                <details class="program-card__accordion">
                    <summary class="program-card__summary">
                        <span>{{ __('home.program_details') }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <ul class="program-card__items">
                        @foreach($program->activeItems as $item)
                        <li class="program-card__item">
                            @if($item->icon)<i class="fas fa-{{ $item->icon }}"></i>@endif
                            <div>
                                <strong>{{ trans_field($item, 'title') }}</strong>
                                @if(trans_field($item, 'description'))<p>{{ trans_field($item, 'description') }}</p>@endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </details>
                @endif
            </article>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:60px 20px">
            <i class="fas fa-cubes" style="font-size:3rem;color:var(--color-text-muted);margin-bottom:16px"></i>
            <p style="color:var(--color-text-muted)">{{ __('common.no_results') }}</p>
        </div>
        @endif
    </div>
</section>
@endsection
