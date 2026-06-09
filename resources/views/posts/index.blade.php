@extends('layouts.app')
@section('content')
<section class="section page-header">
    <div class="container"><h1 class="section-title">{{ $title ?? __('site.nav_news') }}</h1></div>
</section>
<section class="section">
    <div class="container">
        @foreach($posts as $post)
        <article class="news-card" style="margin-bottom:1rem">
            <div class="news-card__date">
                <span class="news-card__day">{{ $post->published_at?->format('d') }}</span>
                <span class="news-card__month">{{ $post->published_at?->format('M') }}</span>
            </div>
            <p><a href="{{ route('posts.show', ['locale' => $currentLocale, 'slug' => $post->slug]) }}">{{ trans_field($post, 'title') }}</a></p>
        </article>
        @endforeach
    </div>
</section>
@endsection
