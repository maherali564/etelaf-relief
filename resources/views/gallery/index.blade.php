@extends('layouts.app')
@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp

@section('title', $page ? trans_field($page, 'title') : __('common.nav_gallery'))
@section('meta_description', $page ? trans_field($page, 'description') : __('common.nav_gallery'))

@section('content')
<section class="page-header">
    <div class="container">
        <span class="section-tag"><i class="fas fa-images"></i> {{ __('common.nav_gallery') }}</span>
        <h1 class="section-title">{{ $page ? trans_field($page, 'title') : __('common.nav_gallery') }}</h1>
        @if($page && trans_field($page, 'description'))
        <p>{{ trans_field($page, 'description') }}</p>
        @endif
    </div>
</section>

<section class="section">
    <div class="container">
        @if(!empty($mediaItems) && count($mediaItems) > 0)
        <div class="gallery__filters" id="galleryFilters">
            <button class="gallery__filter gallery__filter--active" data-filter="all">{{ __('gallery.all') }}</button>
            <button class="gallery__filter" data-filter="image">{{ __('gallery.images') }}</button>
            <button class="gallery__filter" data-filter="video">{{ __('gallery.videos') }}</button>
        </div>
        <div class="gallery__grid" id="galleryGrid">
            @foreach($mediaItems as $item)
            <div class="gallery-card" data-media="{{ $item['media_type'] }}" data-source="{{ $item['source_type'] }}">
                @if($item['media_type'] === 'video')
                @if($item['source_type'] === 'project' || $item['source_type'] === 'story')
                <a href="{{ $item['url'] }}" class="gallery-card__link">
                @elseif($item['video_platform'] === 'youtube' && $item['video_id'])
                <a href="https://www.youtube.com/watch?v={{ $item['video_id'] }}" target="_blank" rel="noopener" class="gallery-card__link">
                @elseif($item['video_platform'] === 'vimeo' && $item['video_id'])
                <a href="https://vimeo.com/{{ $item['video_id'] }}" target="_blank" rel="noopener" class="gallery-card__link">
                @elseif($item['url'])
                <a href="{{ $item['url'] }}" class="gallery-card__link">
                @else
                <div class="gallery-card__link">
                @endif
                    <div class="gallery-card__video">
                        @if($item['thumbnail'])
                        <img loading="lazy" src="{{ asset('storage/'.$item['thumbnail']) }}" alt="{{ $item['title'] }}">
                        @elseif($item['video_platform'] === 'youtube' && $item['video_id'])
                        <img loading="lazy" src="https://img.youtube.com/vi/{{ $item['video_id'] }}/hqdefault.jpg" alt="{{ $item['title'] }}">
                        @elseif($item['video_platform'] === 'vimeo' && $item['video_id'])
                        <img loading="lazy" src="https://vumbnail.com/{{ $item['video_id'] }}.jpg" alt="{{ $item['title'] }}">
                        @endif
                        <div class="gallery-card__play">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    <div class="gallery-card__overlay">
                        <span class="gallery-card__title">{{ $item['title'] }}</span>
                    </div>
                    <span class="gallery-card__badge">
                        <i class="fas fa-video"></i> {{ __('gallery.video') }}
                    </span>
                @if($item['source_type'] === 'project' || $item['source_type'] === 'story' || $item['video_platform'] || $item['url'])
                </a>
                @else
                </div>
                @endif
                @else
                <a href="{{ $item['url'] }}" class="gallery-card__link">
                    <img loading="lazy" src="{{ asset('storage/'.$item['image']) }}" alt="{{ $item['title'] }}">
                    <div class="gallery-card__overlay">
                        <span class="gallery-card__title">{{ $item['title'] }}</span>
                    </div>
                    <span class="gallery-card__badge">
                        @switch($item['source_type'])
                            @case('project') {{ __('common.nav_projects') }} @break
                            @case('news') {{ __('common.nav_news') }} @break
                            @case('story') {{ __('common.nav_stories') }} @break
                            @default {{ __('gallery.image') }}
                        @endswitch
                    </span>
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:60px 20px">
            <i class="fas fa-images" style="font-size:3rem;color:var(--color-text-muted);margin-bottom:16px"></i>
            <p style="color:var(--color-text-muted)">{{ __('common.no_results') }}</p>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    var filters = document.querySelectorAll('.gallery__filter');
    var items = document.querySelectorAll('.gallery-card');
    filters.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filters.forEach(function(b) { b.classList.remove('gallery__filter--active'); });
            this.classList.add('gallery__filter--active');
            var filter = this.getAttribute('data-filter');
            items.forEach(function(item) {
                if (filter === 'all' || item.getAttribute('data-media') === filter) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
})();
</script>
@endpush
