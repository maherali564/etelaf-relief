@extends('layouts.app')
@php
    $allImages = $story->images ?? [];
    if ($story->image && !in_array($story->image, $allImages)) {
        array_unshift($allImages, $story->image);
    }
@endphp
@section('content')
<section class="section">
    <div class="container donate-project">
        <div class="donate-project__grid">
            <div class="donate-project__info">
                @php $galleryCountD = count($allImages); @endphp
                @if($galleryCountD > 0)
                <div class="donate-project__gallery {{ $galleryCountD > 1 ? 'donate-project__gallery--multiple' : 'donate-project__gallery--single' }}" dir="ltr">
                    <div class="donate-project__gallery-main">
                        <img loading="lazy" src="{{ asset('storage/'.$allImages[0]) }}"
                             alt="{{ trans_field($story, 'title') }}"
                             class="donate-project__gallery-main-img"
                             onclick="openLightbox(0)"
                             style="cursor:pointer">
                    </div>
                    @if($galleryCountD > 1)
                    <div class="donate-project__gallery-thumbs">
                        @foreach($allImages as $i => $img)
                        <img loading="lazy" src="{{ asset('storage/'.$img) }}"
                             alt=""
                             class="donate-project__gallery-thumb {{ $i === 0 ? 'donate-project__gallery-thumb--active' : '' }}"
                             onclick="openLightbox({{ $i }})">
                        @endforeach
                    </div>
                    @endif
                </div>
                @elseif($story->image)
                <div class="donate-project__image">
                    <img loading="lazy" src="{{ asset('storage/'.$story->image) }}" alt="{{ trans_field($story, 'title') }}">
                </div>
                @endif
                <h1 class="section-title">{{ trans_field($story, 'title') }}</h1>

                <div class="story__meta" style="color:var(--color-text-muted);margin-bottom:1.5rem">
                    @if($story->person_name)<span><strong>{{ __('common.full_name') }}:</strong> {{ $story->person_name }}</span>@endif
                    @if($story->age)<span style="margin-{{ $isRtl ? 'right' : 'left' }}:1rem"><strong>العمر:</strong> {{ $story->age }} {{ __('common.age') }}</span>@endif
                    @if($story->location)<span style="margin-{{ $isRtl ? 'right' : 'left' }}:1rem"><strong>الموقع:</strong> {{ $story->location }}</span>@endif
                </div>

                <div class="donate-project__description">{!! trans_field($story, 'content') !!}</div>

                @if($story->goal_amount > 0)
                <div class="donate-project__progress">
                    <div class="progress-bar">
                        <div class="progress-bar__fill" style="width:{{ $story->progressPercent() }}%"></div>
                    </div>
                    <div class="progress-stats">
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">{{ __('common.raised') }}</span>
                            <span class="progress-stats__value">${{ number_format($story->raised_amount ?? 0) }}</span>
                        </div>
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">{{ __('common.goal') }}</span>
                            <span class="progress-stats__value">${{ number_format($story->goal_amount) }}</span>
                        </div>
                        <div class="progress-stats__item">
                            <span class="progress-stats__label">%</span>
                            <span class="progress-stats__value">{{ $story->progressPercent() }}%</span>
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
                            <label>{{ __('donate.select_story') }}</label>
                            <select name="story_id" id="storySelect">
                                <option value="">{{ __('donate.select_story') }}</option>
                                @foreach($stories as $st)
                                <option value="{{ $st->id }}">{{ trans_field($st, 'title') }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endsection
                        @section('donate_entity_js')
                        document.getElementById('storySelect').addEventListener('change', function() {
                            if (this.value) {
                                window.location.href = '{{ url($currentLocale . "/donate/story") }}/' + this.value;
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

<div id="lightbox" class="lightbox" onclick="closeLightbox(event)" style="display:none">
    <button class="lightbox__close" onclick="closeLightbox()">&times;</button>
    <button class="lightbox__nav lightbox__nav--prev" onclick="navigateLightbox(-1)">&#8249;</button>
    <img loading="lazy" id="lightbox-img" class="lightbox__img" alt="">
    <button class="lightbox__nav lightbox__nav--next" onclick="navigateLightbox(1)">&#8250;</button>
    <div class="lightbox__counter" id="lightbox-counter"></div>
</div>

@push('scripts')
<script>
const lightboxImages = {!! json_encode(array_map(fn($img) => asset('storage/'.$img), $allImages)) !!};
let currentIndex = 0;

function openLightbox(index) {
    currentIndex = index;
    document.getElementById('lightbox').style.display = 'flex';
    document.getElementById('lightbox-img').src = lightboxImages[index];
    updateCounter();
    document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
    if (e && e.target !== e.currentTarget) return;
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}

function navigateLightbox(dir) {
    currentIndex = (currentIndex + dir + lightboxImages.length) % lightboxImages.length;
    document.getElementById('lightbox-img').src = lightboxImages[currentIndex];
    updateCounter();
}

function updateCounter() {
    document.getElementById('lightbox-counter').textContent =
        (currentIndex + 1) + ' / ' + lightboxImages.length;
}

document.addEventListener('keydown', function(e) {
    if (document.getElementById('lightbox').style.display !== 'flex') return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft' && {{ $isRtl ? 'false' : 'true' }}) navigateLightbox(-1);
    if (e.key === 'ArrowRight' && {{ $isRtl ? 'false' : 'true' }}) navigateLightbox(1);
    if (e.key === 'ArrowLeft' && {{ $isRtl ? 'true' : 'false' }}) navigateLightbox(1);
    if (e.key === 'ArrowRight' && {{ $isRtl ? 'true' : 'false' }}) navigateLightbox(-1);
});
</script>
@endpush
