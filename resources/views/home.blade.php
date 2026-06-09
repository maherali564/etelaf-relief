@extends('layouts.app')
@php $s = $settings; @endphp

@section('content')
    @include('partials.home-hero')
    @include('partials.home-content')
    @include('partials.home-testimonials')
    @include('partials.home-donate')
    @include('partials.home-bottom')
@endsection

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const heroSwiper = new Swiper('.heroSwiper', {
        loop: true,
        autoplay: { delay: 10000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: { crossFade: true },
    });

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

    // Donor Wall: animated counters
    function animateCounter(el, target) {
        var duration = 2000;
        var start = performance.now();
        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = '$' + Math.floor(eased * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = '$' + target.toLocaleString();
        }
        requestAnimationFrame(step);
    }

    function animatePlainCounter(el, target) {
        var duration = 2000;
        var start = performance.now();
        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(eased * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
    }

    var raisedEl = document.getElementById('totalRaised');
    if (raisedEl) animateCounter(raisedEl, parseInt(raisedEl.getAttribute('data-target')));

    var donorsEl = document.getElementById('totalDonors');
    if (donorsEl) animatePlainCounter(donorsEl, parseInt(donorsEl.getAttribute('data-target')));

    var countEl = document.getElementById('totalDonations');
    if (countEl) animatePlainCounter(countEl, parseInt(countEl.getAttribute('data-target')));

    // Donor Wall: soft polling for new donations every 30s
    var feed = document.getElementById('donorFeed');
    if (feed) {
        setInterval(function() {
            fetch('/{{ $currentLocale }}/donations/latest?limit=50')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.html) return;
                    var oldContent = feed.innerHTML;
                    if (data.html !== oldContent) {
                        feed.innerHTML = data.html;
                        // re-count if totals updated
                        if (data.totals) {
                            var re = document.getElementById('totalRaised');
                            if (re) { re.textContent = '$' + parseInt(data.totals.raised).toLocaleString(); re.setAttribute('data-target', data.totals.raised); }
                            var de = document.getElementById('totalDonors');
                            if (de) { de.textContent = parseInt(data.totals.donors).toLocaleString(); de.setAttribute('data-target', data.totals.donors); }
                            var ce = document.getElementById('totalDonations');
                            if (ce) { ce.textContent = parseInt(data.totals.donations).toLocaleString(); ce.setAttribute('data-target', data.totals.donations); }
                        }
                    }
                })
                .catch(function() {});
        }, 30000);
    }
});
</script>
@endpush
