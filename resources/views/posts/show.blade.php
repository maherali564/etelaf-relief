@extends('layouts.app')
@section('content')
<section class="section">
    <div class="container prose">
        <h1>{{ trans_field($post, 'title') }}</h1>
        <p style="color:var(--color-text-muted)">{{ $post->published_at?->format('Y-m-d') }}</p>

        @php
            $allImages = $post->images ?? [];
            if ($post->image && !in_array($post->image, $allImages)) {
                array_unshift($allImages, $post->image);
            }
        @endphp

        @php $galleryCount = count($allImages); @endphp
        @if($galleryCount > 0)
        <div class="story-gallery {{ $galleryCount > 1 ? 'story-gallery--multiple' : 'story-gallery--single' }}" dir="ltr">
            <div class="story-gallery__main">
                <img loading="lazy" src="{{ asset('storage/'.$allImages[0]) }}" alt="" class="story-gallery__main-img" onclick="openLightbox(0)" style="cursor:pointer">
            </div>
            @if($galleryCount > 1)
            <div class="story-gallery__thumbs">
                @foreach($allImages as $i => $img)
                <img loading="lazy" src="{{ asset('storage/'.$img) }}" alt="" class="story-gallery__thumb {{ $i === 0 ? 'story-gallery__thumb--active' : '' }}" onclick="openLightbox({{ $i }})">
                @endforeach
            </div>
            @endif
        </div>
        @elseif($post->image)
        <img loading="lazy" src="{{ asset('storage/'.$post->image) }}" alt="" style="max-width:100%;border-radius:12px;margin:1.5rem 0">
        @endif

        @if($post->video_url)
        @php
            $vType = $post->video_type;
            $vUrl = $post->video_url;
            $vHtml = null;
            if ($vType === 'youtube') {
                preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $vUrl, $m);
                $id = $m[1] ?? $vUrl;
                $vHtml = '<iframe width="100%" height="400" src="https://www.youtube.com/embed/'.$id.'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius:12px;margin:1.5rem 0"></iframe>';
            } elseif ($vType === 'vimeo') {
                preg_match('/vimeo\.com\/(\d+)/', $vUrl, $m);
                $id = $m[1] ?? $vUrl;
                $vHtml = '<iframe width="100%" height="400" src="https://player.vimeo.com/video/'.$id.'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="border-radius:12px;margin:1.5rem 0"></iframe>';
            } else {
                $vHtml = '<video controls style="width:100%;border-radius:12px;margin:1.5rem 0" src="'.e(asset('storage/'.ltrim($vUrl, '/'))).'"></video>';
            }
        @endphp
        {!! $vHtml !!}
        @endif

        <div>{!! trans_field($post, 'content') ?: nl2br(e(trans_field($post, 'excerpt'))) !!}</div>

        @if($post->campaign || $post->type !== 'news')
        <div class="post-donate" style="margin-top:2rem;padding:1.5rem;background:#f8fafc;border-radius:12px;text-align:{{ $isRtl ? 'right' : 'left' }}">
            @if($post->campaign && $post->campaign->goal_amount > 0)
            <div style="margin-bottom:1rem">
                <small style="color:#64748b">{{ __('common.raised') }}: ${{ number_format($post->campaign->raised_amount ?? 0, 0) }} / ${{ number_format($post->campaign->goal_amount, 0) }}</small>
            </div>
            @endif
            <a href="{{ route('donate.post', ['locale' => $currentLocale, 'slug' => $post->slug]) }}" class="btn btn--primary">{{ $post->campaign ? __('common.contribute') : __('common.donate_now') }}</a>
        </div>
        @endif
    </div>
</section>

<div id="lightbox" class="lightbox" onclick="closeLightbox(event)" style="display:none">
    <button class="lightbox__close" onclick="closeLightbox()">&times;</button>
    <button class="lightbox__nav lightbox__nav--prev" onclick="navigateLightbox(-1)">&#8249;</button>
    <img loading="lazy" id="lightbox-img" class="lightbox__img" alt="">
    <button class="lightbox__nav lightbox__nav--next" onclick="navigateLightbox(1)">&#8250;</button>
    <div class="lightbox__counter" id="lightbox-counter"></div>
</div>

<style>
.container.prose { overflow: hidden; }
.story-gallery { max-width: min(420px, 100%); }
.story-gallery--single { margin: 1.5rem auto; }
.story-gallery--multiple { float: {{ $isRtl ? 'right' : 'left' }}; margin-{{ $isRtl ? 'left' : 'right' }}:1.5rem; margin-bottom:1.5rem; }
.story-gallery__main { width:100%; aspect-ratio:16/9; overflow:hidden; border-radius:12px; margin-bottom:12px; }
.story-gallery__main-img { width:100%; height:100%; object-fit:cover; transition:transform 0.3s; }
.story-gallery__main-img:hover { transform:scale(1.03); }
.story-gallery__thumbs { display:flex; gap:10px; flex-wrap:wrap; }
.story-gallery__thumb { width:100px; height:70px; object-fit:cover; border-radius:8px; cursor:pointer; opacity:0.6; transition:all 0.2s; border:2px solid transparent; }
.story-gallery__thumb:hover, .story-gallery__thumb--active { opacity:1; border-color:var(--color-primary, #2563eb); }
.lightbox { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.92); z-index:9999; display:flex; align-items:center; justify-content:center; }
.lightbox__img { max-width:90%; max-height:85%; object-fit:contain; border-radius:4px; box-shadow:0 8px 40px rgba(0,0,0,0.5); }
.lightbox__close { position:absolute; top:20px; {{ $isRtl ? 'left' : 'right' }}:20px; background:none; border:none; color:#fff; font-size:36px; cursor:pointer; opacity:0.8; z-index:10; line-height:1; }
.lightbox__close:hover { opacity:1; }
.lightbox__nav { position:absolute; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:48px; padding:10px 20px; cursor:pointer; border-radius:8px; opacity:0.7; transition:0.2s; line-height:1; }
.lightbox__nav:hover { opacity:1; background:rgba(255,255,255,0.25); }
.lightbox__nav--prev { {{ $isRtl ? 'right' : 'left' }}:20px; }
.lightbox__nav--next { {{ $isRtl ? 'left' : 'right' }}:20px; }
.lightbox__counter { position:absolute; bottom:20px; {{ $isRtl ? 'right' : 'left' }}:50%; transform:translateX({{ $isRtl ? '50%' : '-50%' }}); color:rgba(255,255,255,0.6); font-size:14px; }
</style>

<script>
const lightboxImages = {!! json_encode(array_map(fn($img) => asset('storage/'.$img), $allImages)) !!};
let currentIndex = 0;
function openLightbox(index) { currentIndex=index; document.getElementById('lightbox').style.display='flex'; document.getElementById('lightbox-img').src=lightboxImages[index]; updateCounter(); document.body.style.overflow='hidden'; }
function closeLightbox(e) { if(e&&e.target!==e.currentTarget) return; document.getElementById('lightbox').style.display='none'; document.body.style.overflow=''; }
function navigateLightbox(dir) { currentIndex=(currentIndex+dir+lightboxImages.length)%lightboxImages.length; document.getElementById('lightbox-img').src=lightboxImages[currentIndex]; updateCounter(); }
function updateCounter() { document.getElementById('lightbox-counter').textContent=(currentIndex+1)+' / '+lightboxImages.length; }
document.addEventListener('keydown',function(e){ if(document.getElementById('lightbox').style.display!=='flex') return; if(e.key==='Escape') closeLightbox(); if(e.key==='ArrowLeft') navigateLightbox(-1); if(e.key==='ArrowRight') navigateLightbox(1); });
</script>
@endsection
