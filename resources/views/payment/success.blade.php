@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container payment-center">
        <div class="payment-icon" style="color:#10b981">✅</div>
        <h1 class="section-title" style="margin-bottom:0.5rem">{{ __('donate.donation_success_title') }}</h1>
        <p style="font-size:1.2rem;color:var(--text-muted);margin-bottom:2rem">{{ __('donate.donation_success_message') }}</p>
        <div class="payment-card payment-card--narrow">
            <p><strong>{{ __('donate.donor_name') }}:</strong> {{ $donation->donor_name }}</p>
            <p><strong>{{ __('donate.donation_amount') }}:</strong> ${{ number_format($donation->amount, 2) }}</p>
            <p><strong>{{ __('donate.transaction_id') }}:</strong> {{ $donation->transaction_id }}</p>
            <p><strong>{{ __('donate.donation_date') }}:</strong> {{ $donation->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <div class="payment-actions">
            <a href="{{ route('payment.certificate', ['locale' => $currentLocale, 'donation' => $donation->id, 'token' => request('token')]) }}" class="btn btn--primary">{{ __('common.download_certificate') }}</a>
            <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--lg">{{ __('common.back_to_home') }}</a>
        </div>
    </div>
</section>
@endsection
