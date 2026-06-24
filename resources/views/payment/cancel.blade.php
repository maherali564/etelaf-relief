@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container payment-center">
        <div class="payment-icon" style="color:#f59e0b">⚠️</div>
        <h1 class="section-title" style="margin-bottom:0.5rem">{{ __('donate.donation_failed') }}</h1>
        <p style="font-size:1.2rem;color:var(--text-muted);margin-bottom:2rem">{{ __('common.try_again_later') }}</p>
        <a href="{{ route('donate.page', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--lg">{{ __('common.try_again') }}</a>
    </div>
</section>
@endsection
