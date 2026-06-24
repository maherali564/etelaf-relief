@extends('layouts.app')
@php $isRtl = app()->getLocale() === 'ar'; @endphp
@section('content')

<section class="hero-detailed" style="background:linear-gradient(135deg,#065f46,#059669)">
    <div class="hero-detailed__overlay" style="background:linear-gradient(180deg,rgba(0,0,0,.5),rgba(0,0,0,.25))"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-hand-holding-heart"></i> {{ __('home.donate_tag') }}</span>
        <h1 class="hero-detailed__title">{{ __('home.donate_title') }}</h1>
        <p class="hero-detailed__desc">{{ __('home.donate_desc') }}</p>

    </div>
</section>

<section class="section" style="padding:2rem 0">
    <div class="container">
        <form action="{{ route('donate.store', ['locale' => app()->getLocale()]) }}" method="POST" class="d-row">
            @csrf
            <input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
            @yield('donate_entity_fields')

            <div class="d-col d-col--form">
                <div class="dcard">
                    <div class="dcard__header">
                        <div class="dcard__icon"><i class="fas fa-hand-holding-heart"></i></div>
                        <div>
                            <h2 class="dcard__title">{{ __('common.donation_form') }}</h2>
                            <p class="dcard__sub">{{ __('home.donate_desc') }}</p>
                        </div>
                    </div>

                    <div class="dcard__body">
                        <div class="dsec">
                            <h3 class="dsec__title">{{ __('donate.quick_amounts') }}</h3>
                            <div class="dpreset">
                                @foreach([10, 25, 50, 100, 250, 500] as $preset)
                                <button type="button" class="dpreset__btn" data-amount="{{ $preset }}">${{ $preset }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="dsec">
                            <div class="dgrid dgrid--2">
                                <div class="dfield">
                                    <label class="dfield__label">{{ __('donate.custom_amount') }} <span class="dfield__req">*</span></label>
                                    <input class="dfield__input" type="number" name="amount" id="donationAmount" min="1" step="0.01" required placeholder="{{ __('donate.min_amount') }}">
                                </div>
                                <div class="dfield">
                                    <label class="dfield__label">{{ __('common.full_name') }} <span class="dfield__req">*</span></label>
                                    <input class="dfield__input" type="text" name="donor_name" required placeholder="{{ __('common.full_name') }}">
                                </div>
                                <div class="dfield">
                                    <label class="dfield__label">{{ __('common.email') }} <span class="dfield__req">*</span></label>
                                    <input class="dfield__input" type="email" name="email" required placeholder="example@domain.com">
                                </div>
                                <div class="dfield">
                                    <label class="dfield__label">{{ __('common.phone') }}</label>
                                    <input class="dfield__input" type="tel" name="phone" placeholder="05xxxxxxxx">
                                </div>
                            </div>
                        </div>

                        <div class="dsec">
                            <h3 class="dsec__title">{{ __('donate.payment_method') }}</h3>
                            <select class="dfield__input" name="payment_method_id" id="paymentMethodSelect" required>
                                <option value="">{{ __('donate.select_payment_method') }}</option>
                                @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}" data-driver="{{ $pm->gateway?->driver ?? '' }}">{{ $pm->name }} - {{ $pm->description }}</option>
                                @endforeach
                            </select>
                        </div>

                        @php $cryptoJson = $cryptocurrencies->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'symbol' => $c->symbol, 'networks' => $c->networks->map(fn($n) => ['id' => $n->id, 'name' => $n->network_name])]); @endphp
                        <script>window.cryptoCurrencies = {!! json_encode($cryptoJson) !!};</script>
                        <div id="cryptoSection" class="dsec" style="display:none">
                            <h3 class="dsec__title">{{ __('donate.crypto_payment') }}</h3>
                            <div class="dgrid dgrid--2">
                                <div class="dfield">
                                    <label class="dfield__label">{{ __('donate.select_crypto') }}</label>
                                    <select class="dfield__input" name="cryptocurrency_id" id="cryptoCurrencySelect">
                                        <option value="">{{ __('donate.choose_crypto') }}</option>
                                    </select>
                                </div>
                                <div class="dfield" id="cryptoNetworkGroup" style="display:none">
                                    <label class="dfield__label">{{ __('donate.select_network') }}</label>
                                    <select class="dfield__input" name="crypto_network_id" id="cryptoNetworkSelect">
                                        <option value="">{{ __('donate.choose_network') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="dsec">
                            <div class="dchecks">
                                <label class="dcheck">
                                    <input type="checkbox" name="is_anonymous" value="1">
                                    <span>{{ __('donate.anonymous_donation') }} <small>{{ __('donate.anonymous_hint') }}</small></span>
                                </label>
                                <label class="dcheck">
                                    <input type="checkbox" name="is_recurring" value="1" id="recurringToggle">
                                    <span>{{ __('donate.recurring_donation') }}</span>
                                </label>
                            </div>
                            <div id="recurringOptions" class="dfield" style="display:none;margin-top:10px">
                                <label class="dfield__label">{{ __('donate.recurring_interval') }}</label>
                                <select class="dfield__input" name="recurring_interval">
                                    <option value="monthly">{{ __('donate.every_month') }}</option>
                                    <option value="quarterly">{{ __('donate.every_3_months') }}</option>
                                    <option value="yearly">{{ __('donate.every_year') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="dsec" style="border-bottom:none">
                            <div class="dfield">
                                <label class="dfield__label">{{ __('donate.donation_note') }}</label>
                                <textarea class="dfield__input dfield__input--area" name="notes" rows="3" placeholder="{{ __('donate.donation_note_placeholder') }}"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="dcard__footer">
                        <button type="submit" class="dbtn"><i class="fas fa-heart"></i> {{ __('common.complete_donation') }}</button>
                        <p class="dsecure"><i class="fas fa-lock"></i> {{ __('donate.secure_notice') }}</p>
                    </div>
                </div>
            </div>

            <div class="d-col d-col--side">
                <div class="dcard" style="margin-bottom:1.25rem">
                    <div class="dcard__header">
                        <div class="dcard__icon" style="background:linear-gradient(135deg,#fce7f3,#fbcfe8);color:#db2777"><i class="fas fa-shield-alt"></i></div>
                        <h2 class="dcard__title" style="font-size:1rem">{{ __('donate.why_donate') }}</h2>
                    </div>
                    <div style="padding:0 1.5rem 1.25rem">
                        @foreach(['trusted','transparent','impact','secure'] as $item)
                        <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid #f8fafc">
                            <div style="width:32px;height:32px;background:#ecfdf5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.8rem;color:#059669;flex-shrink:0"><i class="fas fa-{{ $item === 'impact' ? 'heart' : ($item === 'transparent' ? 'eye' : ($item === 'secure' ? 'lock' : 'check-circle')) }}"></i></div>
                            <p style="margin:0;font-size:0.85rem;color:#475569;line-height:1.5">{{ __('donate.why_'.$item) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="dcard" style="text-align:center">
                    <div style="padding:1.5rem">
                        <div style="font-size:2.2rem;color:#059669;margin-bottom:0.5rem"><i class="fas fa-hand-holding-heart"></i></div>
                        <h3 style="margin:0 0 0.25rem;font-size:1rem;font-weight:700;color:#1e293b">{{ __('donate.every_amount_helps') }}</h3>
                        <p style="margin:0;font-size:0.82rem;color:#64748b">{{ __('donate.every_amount_desc') }}</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.d-row{display:flex;gap:1.75rem;align-items:flex-start}
.d-col--form{flex:1.7;min-width:0}
.d-col--side{flex:1;min-width:0}
.dcard{background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #e2e8f0;overflow:hidden}
.dcard__header{display:flex;align-items:center;gap:12px;padding:1.2rem 1.5rem;border-bottom:1px solid #f1f5f9}
.dcard__icon{width:40px;height:40px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#059669;flex-shrink:0}
.dcard__title{margin:0;font-size:1.05rem;font-weight:700;color:#1e293b}
.dcard__sub{margin:2px 0 0;font-size:0.8rem;color:#94a3b8}
.dcard__body{padding:1rem 1.5rem}
.dcard__footer{padding:1rem 1.5rem;background:#f8fafc;border-top:1px solid #e2e8f0}
.dsec{border-bottom:1px solid #f1f5f9;padding:12px 0}
.dsec__title{font-size:0.75rem;font-weight:700;color:#059669;margin:0 0 10px;display:flex;align-items:center;gap:6px;text-transform:uppercase}
.dsec__title::before{content:'';width:3px;height:12px;background:#059669;border-radius:2px}
.dgrid{display:grid;gap:10px}
.dgrid--2{grid-template-columns:1fr 1fr}
.dfield{margin-bottom:0}
.dfield__label{display:block;margin-bottom:4px;font-size:0.75rem;font-weight:600;color:#374151}
.dfield__req{color:#ef4444}
.dfield__input{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.85rem;background:#fafafa;transition:all .25s ease;box-sizing:border-box;color:#1e293b;font-family:inherit}
.dfield__input:hover{border-color:#cbd5e1}
.dfield__input:focus{outline:none;border-color:#10b981;background:#fff;box-shadow:0 0 0 3px rgba(16,185,129,.1)}
.dfield__input--area{resize:vertical;min-height:64px;line-height:1.5;font-family:inherit}
.dpreset{display:flex;flex-wrap:wrap;gap:6px}
.dpreset__btn{padding:8px 18px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;font-size:0.82rem;font-weight:600;color:#475569;cursor:pointer;transition:all .2s ease;font-family:inherit}
.dpreset__btn:hover{border-color:#10b981;color:#059669;background:#f0fdf4}
.dpreset__btn--active{border-color:#059669;background:#059669;color:#fff;box-shadow:0 2px 8px rgba(5,150,105,.25)}
.dchecks{display:flex;flex-direction:column;gap:8px}
.dcheck{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.82rem;color:#475569}
.dcheck input[type=checkbox]{width:16px;height:16px;accent-color:#059669}
.dcheck small{color:#94a3b8}
.dbtn{display:inline-flex;align-items:center;gap:8px;padding:12px 32px;font-size:1rem;font-weight:600;color:#fff;background:linear-gradient(135deg,#059669,#10b981);border:none;border-radius:11px;cursor:pointer;transition:all .3s ease;box-shadow:0 4px 14px rgba(5,150,105,.3);width:100%;justify-content:center;font-family:inherit}
.dbtn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(5,150,105,.4)}
.dbtn:active{transform:translateY(0)}
.dsecure{margin:10px 0 0;font-size:0.75rem;color:#94a3b8;display:flex;align-items:center;gap:5px;justify-content:center}
@media(max-width:900px){.d-row{flex-direction:column-reverse}.d-col--side{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}.dgrid--2{grid-template-columns:1fr}}
@media(max-width:550px){.d-col--side{grid-template-columns:1fr}}
</style>

@push('scripts')
<script>
(function(){
    document.querySelectorAll('.dpreset__btn').forEach(function(b){
        b.addEventListener('click',function(){
            document.querySelectorAll('.dpreset__btn').forEach(function(x){x.classList.remove('dpreset__btn--active')});
            this.classList.add('dpreset__btn--active');
            document.getElementById('donationAmount').value=this.dataset.amount;
        });
    });
    document.getElementById('recurringToggle')?.addEventListener('change',function(){
        document.getElementById('recurringOptions').style.display=this.checked?'block':'none';
    });
    @yield('donate_entity_js')
    var pmSelect=document.getElementById('paymentMethodSelect');
    var cs=document.getElementById('cryptoSection'),cc=document.getElementById('cryptoCurrencySelect'),cn=document.getElementById('cryptoNetworkSelect'),cg=document.getElementById('cryptoNetworkGroup');
    function tc(){
        var sel=pmSelect.options[pmSelect.selectedIndex],d=sel?sel.getAttribute('data-driver'):'';
        cs.style.display=d==='crypto'?'block':'none';cg.style.display='none';cc.innerHTML='<option value="">{{ __('donate.choose_crypto') }}</option>';
        if(d==='crypto'&&window.cryptoCurrencies)window.cryptoCurrencies.forEach(function(c){var o=document.createElement('option');o.value=c.id;o.textContent=c.name+' ('+c.symbol+')';o.setAttribute('data-networks',JSON.stringify(c.networks));cc.appendChild(o)});
    }
    pmSelect.addEventListener('change',tc);
    cc.addEventListener('change',function(){
        var s=this.options[this.selectedIndex],n=s?JSON.parse(s.getAttribute('data-networks')||'[]'):[];
        cn.innerHTML='<option value="">{{ __('donate.choose_network') }}</option>';cg.style.display=n.length?'block':'none';n.forEach(function(n){var o=document.createElement('option');o.value=n.id;o.textContent=n.name;cn.appendChild(o)});
    });
})();
</script>
@endpush
@endsection