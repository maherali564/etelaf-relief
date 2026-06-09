@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container payment-center payment-center--sm">
        @php $isCrypto = ($donation->paymentMethod?->gateway?->driver ?? '') === 'crypto'; @endphp

        <div class="payment-icon payment-icon--sm" style="color:var(--primary)">
            {{ $isCrypto ? '₿' : '🏦' }}
        </div>
        <h1 class="section-title" style="margin-bottom:0.5rem">
            {{ $isCrypto ? __('donate.crypto_payment') : __('donate.bank_transfer') }}
        </h1>
        <p style="color:var(--text-muted);margin-bottom:2rem">{{ $isCrypto ? __('donate.crypto_instructions') : __('donate.transfer_instructions') }}</p>

        @if($paymentMethod && $paymentMethod->name)
        <p class="payment-amount">{{ $paymentMethod->name }}</p>
        @endif

        @if($instructions)
        <div class="payment-card" style="text-align:right">
            <p style="white-space:pre-wrap;text-align:start">{{ $instructions }}</p>
        </div>
        @endif

        @if($isCrypto)
            @php $crypto = $config; @endphp

            @if(!empty($crypto['qr_code']))
            <div style="margin-bottom:2rem">
                <p style="font-weight:bold;margin-bottom:0.5rem">{{ __('donate.scan_qr') }}</p>
                <img src="{{ asset('storage/'.$crypto['qr_code']) }}" alt="QR Code"
                     style="max-width:250px;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,0.1);background:#fff;padding:1rem">
            </div>
            @endif

            <div class="payment-card">
                <table class="payment-table">
                    @if(!empty($crypto['wallet_address']))
                    <tr>
                        <td class="payment-td-label">{{ __('donate.wallet_address') }}</td>
                        <td class="payment-td">
                            <code class="payment-mono" style="display:inline-block;padding:0.5rem 1rem;border-radius:8px;font-size:0.85rem;background:var(--bg)">{{ $crypto['wallet_address'] }}</code>
                            <button onclick="navigator.clipboard.writeText('{{ $crypto['wallet_address'] }}');this.textContent='{{ __("common.copied") }}'" class="payment-copy-btn">{{ __('common.copy_link') }}</button>
                        </td>
                    </tr>
                    @endif
                    @if(!empty($crypto['network']))
                    <tr><td class="payment-td-label">{{ __('donate.network') }}</td><td class="payment-td"><span class="network-badge">{{ $crypto['network'] }}</span></td></tr>
                    @endif
                    @if(!empty($crypto['currency_symbol']))
                    <tr><td class="payment-td-label">{{ __('donate.currency') }}</td><td class="payment-td">{{ $crypto['currency_symbol'] }}</td></tr>
                    @endif
                    @if(!empty($crypto['additional_info']))
                    <tr><td class="payment-td-label">{{ __('donate.additional_info') }}</td><td class="payment-td" style="white-space:pre-wrap">{{ $crypto['additional_info'] }}</td></tr>
                    @endif
                </table>
            </div>
        @else
            @if(!empty($config['bank_name']) || !empty($config['account_number']))
            <div class="payment-card">
                <table class="payment-table">
                    @if(!empty($config['bank_name']))
                    <tr><td class="payment-td-label">{{ __('donate.bank_name') }}</td><td class="payment-td">{{ $config['bank_name'] }}</td></tr>
                    @endif
                    @if(!empty($config['account_name']))
                    <tr><td class="payment-td-label">{{ __('donate.account_name') }}</td><td class="payment-td">{{ $config['account_name'] }}</td></tr>
                    @endif
                    @if(!empty($config['account_number']))
                    <tr><td class="payment-td-label">{{ __('donate.account_number') }}</td><td class="payment-td payment-mono">{{ $config['account_number'] }}</td></tr>
                    @endif
                    @if(!empty($config['iban']))
                    <tr><td class="payment-td-label">{{ __('donate.iban') }}</td><td class="payment-td payment-mono">{{ $config['iban'] }}</td></tr>
                    @endif
                    @if(!empty($config['swift_code']))
                    <tr><td class="payment-td-label">{{ __('donate.swift_code') }}</td><td class="payment-td payment-mono">{{ $config['swift_code'] }}</td></tr>
                    @endif
                </table>
            </div>
            @endif
        @endif

        <div class="payment-card payment-card--compact">
            <p><strong>{{ __('donate.donation_amount') }}:</strong> ${{ number_format($donation->amount, 2) }}</p>
            <p><strong>{{ __('donate.donation_for') }}:</strong> {{ $donation->donor_name }}</p>
            <p><strong>{{ __('donate.transaction_id') }}:</strong> {{ $donation->transaction_id }}</p>
            @if(isset($crypto) && !empty($crypto['currency_symbol']))
            <p><strong>{{ __('donate.crypto_amount') }}:</strong> {{ number_format($donation->amount * ((float)($crypto['conversion_rate'] ?? 1)), 6) }} {{ $crypto['currency_symbol'] }}</p>
            @endif
        </div>

        <p style="color:var(--text-muted);margin-bottom:2rem">{{ $isCrypto ? __('donate.after_crypto_notice') : __('donate.after_transfer_notice') }}</p>
        <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--lg">{{ __('common.back_to_home') }}</a>
    </div>
</section>
@endsection
