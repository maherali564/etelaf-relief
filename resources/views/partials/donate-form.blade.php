@csrf
<input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

@yield('donate_entity_fields')

<div class="form-group">
    <label>{{ __('donate.quick_amounts') }}</label>
    <div class="amount-presets">
        @foreach([10, 25, 50, 100, 250, 500] as $preset)
        <button type="button" class="amount-preset" data-amount="{{ $preset }}">${{ $preset }}</button>
        @endforeach
    </div>
</div>

<div class="form-group">
    <label>{{ __('donate.custom_amount') }}</label>
    <input type="number" name="amount" id="donationAmount" min="1" step="0.01" required placeholder="{{ __('donate.min_amount') }}">
</div>

<div class="form-group">
    <label>{{ __('common.full_name') }}</label>
    <input type="text" name="donor_name" required>
</div>

<div class="form-group">
    <label>{{ __('common.email') }}</label>
    <input type="email" name="email" required>
</div>

<div class="form-group">
    <label>{{ __('common.phone') }}</label>
    <input type="tel" name="phone">
</div>

<div class="form-group">
    <label>{{ __('donate.payment_method') }}</label>
    <select name="payment_method_id" id="paymentMethodSelect" required>
        <option value="">{{ __('donate.select_payment_method') }}</option>
        @foreach($paymentMethods as $pm)
        <option value="{{ $pm->id }}" data-driver="{{ $pm->gateway?->driver ?? '' }}">{{ $pm->name }} - {{ $pm->description }}</option>
        @endforeach
    </select>
</div>

@php $cryptoJson = $cryptocurrencies->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'symbol' => $c->symbol, 'networks' => $c->networks->map(fn($n) => ['id' => $n->id, 'name' => $n->network_name])]); @endphp
<script>window.cryptoCurrencies = {!! json_encode($cryptoJson) !!};</script>
<div id="cryptoSection" class="crypto-selection" style="display:none">
    <div class="form-group">
        <label>{{ __('donate.select_crypto') }}</label>
        <select name="cryptocurrency_id" id="cryptoCurrencySelect">
            <option value="">{{ __('donate.choose_crypto') }}</option>
        </select>
    </div>
    <div class="form-group" id="cryptoNetworkGroup" style="display:none">
        <label>{{ __('donate.select_network') }}</label>
        <select name="crypto_network_id" id="cryptoNetworkSelect">
            <option value="">{{ __('donate.choose_network') }}</option>
        </select>
    </div>
</div>

<div class="form-checkboxes">
    <label class="checkbox-label">
        <input type="checkbox" name="is_anonymous" value="1">
        <span>{{ __('donate.anonymous_donation') }} <small>({{ __('donate.anonymous_hint') }})</small></span>
    </label>
    <label class="checkbox-label">
        <input type="checkbox" name="is_recurring" value="1" id="recurringToggle">
        <span>{{ __('donate.recurring_donation') }}</span>
    </label>
</div>

<div id="recurringOptions" class="form-group" style="display:none">
    <label>{{ __('donate.recurring_interval') }}</label>
    <select name="recurring_interval">
        <option value="monthly">{{ __('donate.every_month') }}</option>
        <option value="quarterly">{{ __('donate.every_3_months') }}</option>
        <option value="yearly">{{ __('donate.every_year') }}</option>
    </select>
</div>

<div class="form-group">
    <label>{{ __('donate.donation_note') }}</label>
    <textarea name="notes" rows="2"></textarea>
</div>

<button type="submit" class="btn btn--primary btn--block btn--lg">{{ __('common.complete_donation') }}</button>

@push('scripts')
<script>
(function() {
    const presets = document.querySelectorAll('.amount-preset');
    const amountInput = document.getElementById('donationAmount');
    presets.forEach(btn => {
        btn.addEventListener('click', function() {
            presets.forEach(b => b.classList.remove('amount-preset--active'));
            this.classList.add('amount-preset--active');
            amountInput.value = this.dataset.amount;
        });
    });
    document.getElementById('recurringToggle')?.addEventListener('change', function() {
        document.getElementById('recurringOptions').style.display = this.checked ? 'block' : 'none';
    });

    @yield('donate_entity_js')

    var pmSelect = document.getElementById('paymentMethodSelect');
    var cryptoSec = document.getElementById('cryptoSection');
    var cryptoCurSelect = document.getElementById('cryptoCurrencySelect');
    var cryptoNetSelect = document.getElementById('cryptoNetworkSelect');
    var cryptoNetGroup = document.getElementById('cryptoNetworkGroup');

    function toggleCrypto() {
        var sel = pmSelect.options[pmSelect.selectedIndex];
        var driver = sel ? sel.getAttribute('data-driver') : '';
        var isCrypto = driver === 'crypto';
        cryptoSec.style.display = isCrypto ? 'block' : 'none';
        cryptoNetGroup.style.display = 'none';
        cryptoCurSelect.innerHTML = '<option value="">{{ __('donate.choose_crypto') }}</option>';
        if (isCrypto && window.cryptoCurrencies) {
            window.cryptoCurrencies.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name + ' (' + c.symbol + ')';
                opt.setAttribute('data-networks', JSON.stringify(c.networks));
                cryptoCurSelect.appendChild(opt);
            });
        }
    }

    pmSelect.addEventListener('change', toggleCrypto);
    toggleCrypto();

    cryptoCurSelect.addEventListener('change', function() {
        var sel = this.options[this.selectedIndex];
        var networks = sel ? JSON.parse(sel.getAttribute('data-networks') || '[]') : [];
        cryptoNetSelect.innerHTML = '<option value="">{{ __('donate.choose_network') }}</option>';
        cryptoNetGroup.style.display = networks.length ? 'block' : 'none';
        networks.forEach(function(n) {
            var opt = document.createElement('option');
            opt.value = n.id;
            opt.textContent = n.name;
            cryptoNetSelect.appendChild(opt);
        });
    });
})();
</script>
@endpush
