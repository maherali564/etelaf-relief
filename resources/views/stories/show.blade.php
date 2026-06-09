@extends('layouts.app')
@section('content')
<section class="section">
    <div class="container prose">
        <h1 class="section-title">{{ trans_field($story, 'title') }}</h1>

        @php
            $allImages = $story->images ?? [];
            if ($story->image && !in_array($story->image, $allImages)) {
                array_unshift($allImages, $story->image);
            }
            $galleryCount = count($allImages);
        @endphp

        @if($galleryCount > 0)
        <div class="story-gallery {{ $galleryCount > 1 ? 'story-gallery--multiple' : 'story-gallery--single' }}" dir="ltr">
            <div class="story-gallery__main">
                <img loading="lazy" src="{{ asset('storage/'.$allImages[0]) }}"
                     alt=""
                     class="story-gallery__main-img"
                     onclick="openLightbox(0)"
                     style="cursor:pointer">
            </div>
            @if($galleryCount > 1)
            <div class="story-gallery__thumbs">
                @foreach($allImages as $i => $img)
                <img loading="lazy" src="{{ asset('storage/'.$img) }}"
                     alt=""
                     class="story-gallery__thumb {{ $i === 0 ? 'story-gallery__thumb--active' : '' }}"
                     onclick="openLightbox({{ $i }})">
                @endforeach
            </div>
            @endif
        </div>
        @elseif($story->image)
        <img loading="lazy" src="{{ asset('storage/'.$story->image) }}" alt="" class="story__image">
        @endif

        @if($story->video_url)
        @php
            $vType = $story->video_type;
            $vUrl = $story->video_url;
            $vHtml = null;
            if ($vType === 'youtube') {
                preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $vUrl, $m);
                $id = $m[1] ?? $vUrl;
                $vHtml = '<iframe width="100%" height="400" src="https://www.youtube.com/embed/'.$id.'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="story__video"></iframe>';
            } elseif ($vType === 'vimeo') {
                preg_match('/vimeo\.com\/(\d+)/', $vUrl, $m);
                $id = $m[1] ?? $vUrl;
                $vHtml = '<iframe width="100%" height="400" src="https://player.vimeo.com/video/'.$id.'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen class="story__video"></iframe>';
            } else {
                $vHtml = '<video controls class="story__video" src="'.e(asset('storage/'.ltrim($vUrl, '/'))).'"></video>';
            }
        @endphp
        {!! $vHtml !!}
        @endif

        <div class="story-meta">
            @if($story->person_name)<span><strong>{{ __('common.full_name') }}:</strong> {{ $story->person_name }}</span>@endif
            @if($story->age)<span class="story-meta__sep" style="margin-{{ $isRtl ? 'right' : 'left' }}:1rem"><strong>العمر:</strong> {{ $story->age }}</span>@endif
            @if($story->location)<span class="story-meta__sep" style="margin-{{ $isRtl ? 'right' : 'left' }}:1rem"><strong>الموقع:</strong> {{ $story->location }}</span>@endif
        </div>
        <div>{!! trans_field($story, 'content') !!}</div>

        @if($story->goal_amount > 0)
        <div class="project-progress" style="margin:2rem 0">
            <div class="progress-track">
                <div class="progress-bar__fill" style="width:{{ $story->progressPercent() }}%;height:100%;background:var(--color-primary, #2563eb);border-radius:6px;transition:width 0.5s"></div>
            </div>
            <div class="progress-stats">
                <div><span class="progress-label">{{ __('common.raised') }}</span><span class="progress-value">${{ number_format($story->raised_amount ?? 0) }}</span></div>
                <div><span class="progress-label">{{ __('common.goal') }}</span><span class="progress-value">${{ number_format($story->goal_amount) }}</span></div>
                <div><span class="progress-label">%</span><span class="progress-value">{{ $story->progressPercent() }}%</span></div>
            </div>
        </div>
        @endif

        <a href="{{ route('donate.story', ['locale' => $currentLocale, 'id' => $story->id]) }}" class="btn btn--primary story-cta">{{ __('common.contribute') }}</a>
    </div>
</section>

@php $storyDonations = $story->donations()->completed()->latest()->limit(20)->get(); @endphp
@if($storyDonations->isNotEmpty())
<section class="section story-donors-section">
    <div class="container">
        <div class="story-donors-header">
            <h2 class="section-title">{{ __('donor_wall.recent_donations') }}</h2>
        </div>
        <div class="donor-wall__compact">
            @foreach($storyDonations as $d)
            <div class="donor-entry">
                <div class="donor-entry__avatar" style="width:36px;height:36px;font-size:0.85rem;background:linear-gradient(135deg,#059669,#10b981)">
                    {{ strtoupper(substr($d->is_anonymous ? __('common.anonymous') : $d->donor_name, 0, 1)) }}
                </div>
                <div class="donor-entry__info">
                    <strong class="donor-entry__name" style="font-size:0.9rem;color:#1e293b">{{ $d->is_anonymous ? __('common.anonymous') : $d->donor_name }}</strong>
                    <span class="donor-entry__meta" style="font-size:0.8rem;color:#64748b">{{ $d->donated_at?->diffForHumans() ?: $d->created_at->diffForHumans() }}</span>
                </div>
                <span class="donor-entry__amount" style="font-size:1rem;color:#059669">${{ number_format($d->amount, 0) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Lightbox --}}
<div id="lightbox" class="lightbox" onclick="closeLightbox(event)" style="display:none">
    <button class="lightbox__close" onclick="closeLightbox()">&times;</button>
    <button class="lightbox__nav lightbox__nav--prev" onclick="navigateLightbox(-1)">&#8249;</button>
    <img loading="lazy" id="lightbox-img" class="lightbox__img" alt="">
    <button class="lightbox__nav lightbox__nav--next" onclick="navigateLightbox(1)">&#8250;</button>
    <div class="lightbox__counter" id="lightbox-counter"></div>
</div>

<style>
.story-gallery { max-width: min(420px, 100%); }
.story-gallery--single { margin: 1.5rem auto; }
.story-gallery--multiple {
    float: {{ $isRtl ? 'right' : 'left' }};
    margin-{{ $isRtl ? 'left' : 'right' }}: 1.5rem;
    margin-bottom: 1.5rem;
}
.story-gallery__main {
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
    border-radius: 12px;
    margin-bottom: 12px;
}
.story-gallery__main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.story-gallery__main-img:hover { transform: scale(1.03); }
.story-gallery__thumbs { display: flex; gap: 10px; flex-wrap: wrap; }
.story-gallery__thumb {
    width: 100px; height: 70px; object-fit: cover; border-radius: 8px;
    cursor: pointer; opacity: 0.6; transition: all 0.2s; border: 2px solid transparent;
}
.story-gallery__thumb:hover,
.story-gallery__thumb--active { opacity: 1; border-color: var(--color-primary, #2563eb); }
.container.prose { overflow: hidden; }
.lightbox__close {
    position: absolute; top: 20px;
    {{ $isRtl ? 'left' : 'right' }}: 20px;
    background: none; border: none; color: #fff; font-size: 36px;
    cursor: pointer; opacity: 0.8; z-index: 10; line-height: 1;
}
.lightbox__close:hover { opacity: 1; }
.lightbox__nav--prev { {{ $isRtl ? 'right' : 'left' }}: 20px; }
.lightbox__nav--next { {{ $isRtl ? 'left' : 'right' }}: 20px; }
.lightbox__counter {
    position: absolute; bottom: 20px;
    {{ $isRtl ? 'right' : 'left' }}: 50%;
    transform: translateX({{ $isRtl ? '50%' : '-50%' }});
    color: rgba(255,255,255,0.6); font-size: 14px;
}
.donor-wall__compact { max-width:600px; margin:0 auto; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; background:#fff; }
.donor-wall__compact .donor-entry { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-bottom:1px solid #f1f5f9; }
.donor-wall__compact .donor-entry:last-child { border-bottom:none; }
.donor-wall__compact .donor-entry:hover { background:#f8fafc; }
</style>

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
@endsection
