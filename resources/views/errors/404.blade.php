@extends('layouts.app')

@section('content')
<section class="section error-section">
    <div class="container error-container">
        <h1 class="error-code" style="color:var(--color-primary)">404</h1>
        <h2 class="error-title">{{ __('errors.not_found_title') }}</h2>
        <p class="error-desc" style="color:var(--color-text-muted)">{{ __('errors.not_found_desc') }}</p>
        <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--lg">{{ __('errors.back_home') }}</a>
    </div>
</section>
@endsection
