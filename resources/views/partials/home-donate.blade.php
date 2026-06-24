<section class="donate section" id="donate">
    <div class="container donate__inner {{ $isRtl ? 'donate__inner--rtl' : 'donate__inner--ltr' }}">
        <div class="donate__content {{ $isRtl ? 'donate__content--rtl' : 'donate__content--ltr' }}">
            <div class="section-tag section-tag--light"><i class="fas fa-hand-holding-heart"></i> {{ __('home.donate_tag') }}</div>
            <h2 class="section-title section-title--light">{{ __('home.donate_title') }}</h2>
            <p class="section-desc" style="color:rgba(251,248,242,0.7);max-width:520px">{{ __('home.donate_desc') }}</p>

            <div class="donate__methods">
                <h3>{{ __('common.payment_methods') }}</h3>
                <div class="payment-methods-grid">
                    @foreach($paymentMethods as $pm)
                    <div class="payment-method-card" data-method-id="{{ $pm->id }}">
                        <div class="payment-method-card__icon">
                            @if($pm->gateway && $pm->gateway->logo)
                            <img loading="lazy" src="{{ asset('storage/'.$pm->gateway->logo) }}" alt="{{ $pm->name }}">
                            @elseif($pm->icon)
                            <i class="{{ $pm->icon }}"></i>
                            @else
                            <i class="fas fa-wallet"></i>
                            @endif
                        </div>
                        <div class="payment-method-card__info">
                            <strong>{{ $pm->name }}</strong>
                            @if($pm->description)
                            <span>{{ $pm->description }}</span>
                            @endif
                        </div>
                        @if($pm->gateway && $pm->gateway->driver === 'crypto')
                        <span class="payment-method-card__badge">Crypto</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <form class="donate-form {{ $isRtl ? 'donate-form--rtl' : 'donate-form--ltr' }}" action="{{ route('donate.store', ['locale' => $currentLocale]) }}" method="POST">
            @csrf
<input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

            <h3>{{ __('common.donation_form') }}</h3>

            <div class="donate-form__presets">
                <span>{{ __('donate.quick_amounts') }}</span>
                <div class="amount-presets">
                    @foreach([10, 25, 50, 100, 250, 500] as $preset)
                    <button type="button" class="amount-preset" data-amount="{{ $preset }}">${{ $preset }}</button>
                    @endforeach
                </div>
            </div>
            <label>
                <span>{{ __('donate.custom_amount') }}</span>
                <input type="number" name="amount" id="donationAmount" min="1" step="0.01" required placeholder="{{ __('donate.min_amount') }}">
            </label>
            <label>
                <span>{{ __('common.full_name') }}</span>
                <input type="text" name="donor_name" required>
            </label>
            <label>
                <span>{{ __('common.email') }}</span>
                <input type="email" name="email" required>
            </label>
            <label>
                <span>{{ __('common.phone') }}</span>
                <input type="tel" name="phone">
            </label>
            <label>
                <span>{{ __('donate.payment_method') }}</span>
                <select name="payment_method_id" id="paymentMethodSelect">
                    <option value="">{{ __('donate.general_donation') }}</option>
                    @foreach($paymentMethods as $pm)
                    <option value="{{ $pm->id }}" data-driver="{{ $pm->gateway?->driver ?? '' }}">{{ $pm->name }} - {{ $pm->description }}</option>
                    @endforeach
                </select>
                <div id="paymentMethodInfo" class="payment-method-info" style="display:none"></div>
            </label>

            @php $cryptoJson = $cryptocurrencies->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'symbol' => $c->symbol, 'networks' => $c->networks->map(fn($n) => ['id' => $n->id, 'name' => $n->network_name])]); @endphp
            <script>window.cryptoCurrencies = {!! json_encode($cryptoJson) !!};</script>
            <div id="cryptoSection" class="crypto-selection" style="display:none">
                <label>
                    <span>{{ __('donate.select_crypto') }}</span>
                    <select name="cryptocurrency_id" id="cryptoCurrencySelect">
                        <option value="">{{ __('donate.choose_crypto') }}</option>
                    </select>
                </label>
                <label id="cryptoNetworkGroup" style="display:none">
                    <span>{{ __('donate.select_network') }}</span>
                    <select name="crypto_network_id" id="cryptoNetworkSelect">
                        <option value="">{{ __('donate.choose_network') }}</option>
                    </select>
                </label>
            </div>

            <div class="donate-form__options">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_anonymous" value="1">
                    <span>{{ __('donate.anonymous_donation') }}</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_recurring" value="1" id="recurringToggle">
                    <span>{{ __('donate.recurring_donation') }}</span>
                </label>
            </div>
            <div id="recurringOptions" class="donate-form__recurring" style="display:none">
                <label>
                    <span>{{ __('donate.recurring_interval') }}</span>
                    <select name="recurring_interval">
                        <option value="monthly">{{ __('donate.every_month') }}</option>
                        <option value="quarterly">{{ __('donate.every_3_months') }}</option>
                        <option value="yearly">{{ __('donate.every_year') }}</option>
                    </select>
                </label>
            </div>
            <label>
                <span>{{ __('donate.donation_note') }}</span>
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit" class="btn btn--primary btn--block btn--lg">{{ __('common.complete_donation') }}</button>
        </form>
    </div>
</section>
