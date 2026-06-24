@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container payment-center" style="max-width:700px;margin:0 auto">
        @php $isCrypto = $driver === 'crypto'; @endphp

        <div class="payment-icon payment-icon--sm" style="color:var(--primary)">
            {{ $isCrypto ? '₿' : '🏦' }}
        </div>
        <h1 class="section-title" style="margin-bottom:0.5rem">
            {{ $isCrypto ? __('donate.crypto_payment') : __('donate.bank_transfer') }}
        </h1>
        <p style="color:var(--text-muted);margin-bottom:2rem">
            {{ $isCrypto ? __('donate.crypto_instructions') : __('donate.transfer_instructions') }}
        </p>

        @if($paymentMethod && $paymentMethod->name)
        <p class="payment-amount">{{ $paymentMethod->name }}</p>
        @endif

        {{-- Bank Transfer Details --}}
        @if(!$isCrypto)
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
                <tr><td class="payment-td-label">IBAN</td><td class="payment-td payment-mono">
                    {{ $config['iban'] }}
                    <button onclick="navigator.clipboard.writeText('{{ $config['iban'] }}');this.textContent='{{ __("common.copied") }}'" class="payment-copy-btn payment-copy-btn--sm" style="margin-right:0.5rem">{{ __('common.copy_link') }}</button>
                </td></tr>
                @endif
                @if(!empty($config['swift_code']))
                <tr><td class="payment-td-label">SWIFT</td><td class="payment-td payment-mono">{{ $config['swift_code'] }}</td></tr>
                @endif
                @if(!empty($config['email']))
                <tr><td class="payment-td-label">{{ __('donate.email') }}</td><td class="payment-td payment-mono">{{ $config['email'] }}</td></tr>
                @endif
            </table>
            @if($instructions)
            <div class="payment-instructions-box">
                <p style="white-space:pre-wrap;margin:0">{{ $instructions }}</p>
            </div>
            @endif
        </div>

        {{-- Bank Transfer Form --}}
        <div class="payment-card" style="text-align:start">
            <h3 style="margin-bottom:1.5rem;color:var(--primary)">{{ __('donate.confirm_transfer') }}</h3>
            <form method="POST" action="{{ route('payment.confirm', ['locale' => $donation->locale, 'donation' => $donation->id]) }}" enctype="multipart/form-data">
                @csrf
                <div class="payment-label">
                    <label style="display:block;font-weight:bold;margin-bottom:0.5rem">{{ __('donate.reference_number') }}</label>
                    <input type="text" name="reference_number" class="payment-input" required>
                </div>
                <div class="payment-form-grid">
                    <div>
                        <label class="payment-label">{{ __('donate.amount') }}</label>
                        <input type="number" name="amount" step="0.01" value="{{ $donation->amount }}" class="payment-input">
                    </div>
                    <div>
                        <label class="payment-label">{{ __('donate.currency') }}</label>
                        <input type="text" name="currency" value="{{ $donation->currency }}" class="payment-input">
                    </div>
                </div>
                <div style="margin-bottom:1rem">
                    <label class="payment-label">{{ __('donate.transfer_date') }}</label>
                    <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="payment-input">
                </div>
                <div style="margin-bottom:1rem">
                    <label class="payment-label">{{ __('donate.proof_document') }}</label>
                    <input type="file" name="proof_document" accept=".jpg,.jpeg,.png,.pdf" style="width:100%;padding:0.75rem;border:1px solid #e2e8f0;border-radius:8px;font-size:1rem;background:#fff">
                    <p class="payment-hint">{{ __('donate.proof_hint') }}</p>
                </div>
                <div style="margin-bottom:1.5rem">
                    <label class="payment-label">{{ __('donate.notes') }}</label>
                    <textarea name="notes" rows="3" class="payment-input"></textarea>
                </div>
                <button type="submit" class="btn btn--primary btn--block btn--lg">
                    {{ __('donate.submit_confirmation') }}
                </button>
            </form>
        </div>
        @endif

        {{-- Crypto Wallet Info --}}
        @if($isCrypto)
            @if($selectedNetwork)
            <div class="payment-card" style="text-align:start">
                <p style="font-size:1.1rem;font-weight:bold;color:var(--primary);margin-bottom:0.5rem">
                    {{ $selectedNetwork->cryptocurrency?->symbol }} - {{ $selectedNetwork->network_name }}
                </p>
                <p style="color:var(--text-muted);margin-bottom:1.5rem;font-size:0.9rem">
                    {{ __('donate.send_exact_amount') }}: <strong>${{ number_format($donation->amount, 2) }}</strong>
                </p>

                <div class="payment-wallet-box">
                    <p style="font-weight:bold;margin-bottom:0.5rem">{{ __('donate.wallet_address') }}</p>
                    <p id="walletAddress" class="payment-wallet-address">{{ $selectedNetwork->wallet_address }}</p>
                    <button onclick="navigator.clipboard.writeText(document.getElementById('walletAddress').textContent);this.textContent='{{ __("common.copied") }}'" class="payment-copy-btn">{{ __('common.copy_link') }}</button>

                    @if($selectedNetwork->qr_code)
                    <div style="margin-top:1rem;text-align:center">
                        <img src="{{ asset('storage/' . $selectedNetwork->qr_code) }}" alt="QR" style="max-width:150px;border-radius:8px">
                    </div>
                    @endif
                </div>

                <div class="payment-notice-box">
                    <p style="margin:0;font-size:0.95rem;color:#92400e">
                        {{ __('donate.auto_confirm_notice') }}
                    </p>
                </div>
            </div>
            @else
            <div class="payment-card" style="text-align:center">
                <p style="color:var(--text-muted)">{{ __('donate.crypto_select_first') }}</p>
            </div>
            @endif
        @endif

        {{-- Donation Info --}}
        <div class="payment-card payment-card--compact" style="text-align:start">
            <p><strong>{{ __('donate.donation_amount') }}:</strong> ${{ number_format($donation->amount, 2) }}</p>
            <p><strong>{{ __('donate.donation_for') }}:</strong> {{ $donation->donor_name }}</p>
            <p><strong>{{ __('donate.transaction_id') }}:</strong> {{ $donation->transaction_id }}</p>
            <p><strong>{{ __('donate.status') }}:</strong> <span style="color:#d97706">{{ __('donate.status_pending') }}</span></p>
            @if($donation->project)<p><strong>{{ __('common.nav_projects') }}:</strong> {{ trans_field($donation->project, 'title') }}</p>@endif
            @if($donation->story)<p><strong>{{ __('common.nav_stories') }}:</strong> {{ trans_field($donation->story, 'title') }}</p>@endif

        </div>
    </div>
</section>
@endsection
