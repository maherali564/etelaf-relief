<!DOCTYPE html>
@php
    $currentLocale = $currentLocale ?? app()->getLocale();
    $isRtl = $isRtl ?? ($currentLocale === 'ar');
    $supportedLocales = $supportedLocales ?? config('app.supported_locales', ['ar', 'en', 'es', 'id', 'tr']);
    $localeLabels = $localeLabels ?? [
        'ar' => 'العربية',
        'en' => 'English',
        'es' => 'Español',
        'id' => 'Bahasa Indonesia',
        'tr' => 'Türkçe',
        'sv' => 'Svenska',
    ];
    $localeFlags = $localeFlags ?? [
        'ar' => '🇸🇦',
        'en' => '🇬🇧',
        'es' => '🇪🇸',
        'id' => '🇮🇩',
        'tr' => '🇹🇷',
        'sv' => '🇸🇪',
    ];
@endphp
<html lang="{{ $currentLocale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', trans_field($settings ?? null, 'site_name', $currentLocale) ?? config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', trans_field($settings ?? null, 'tagline', $currentLocale) ?? '')">
    <meta property="og:title" content="@yield('title', trans_field($settings ?? null, 'site_name', $currentLocale) ?? config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', trans_field($settings ?? null, 'tagline', $currentLocale) ?? '')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('storage/' . (($settings->logos[$currentLocale] ?? $settings->logo ?? ''))))">
    <meta property="og:locale" content="{{ $currentLocale === 'ar' ? 'ar_AR' : ($currentLocale === 'sv' ? 'sv_SE' : $currentLocale . '_' . strtoupper($currentLocale)) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', trans_field($settings ?? null, 'site_name', $currentLocale) ?? config('app.name'))">
    <meta name="twitter:description" content="@yield('meta_description', trans_field($settings ?? null, 'tagline', $currentLocale) ?? '')">
    <link rel="canonical" href="{{ url()->current() }}">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NGO",
        "name": "{{ trans_field($settings ?? null, 'site_name', $currentLocale) ?? config('app.name') }}",
        "description": "{{ trans_field($settings ?? null, 'tagline', $currentLocale) ?? '' }}",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('storage/' . (($settings->logos[$currentLocale] ?? $settings->logo ?? ''))) }}",
        "foundingDate": "2024",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "{{ $currentLocale === 'sv' ? 'SE' : 'SA' }}"
        }
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/extra.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    @livewireStyles
    @stack('head')
</head>
<body class="{{ $isRtl ? 'rtl' : 'ltr' }}">
    @include('partials.top-bar')
    @include('partials.header')

    @if(session('success'))
        <div class="alert alert--success container">{{ session('success') }}</div>
    @endif

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <button id="scrollToTop" class="scroll-top" aria-label="Scroll to top"><i class="fas fa-arrow-up"></i></button>
    <div id="darkModeToggle" class="dark-mode-toggle" role="button" aria-label="Toggle dark mode"><i class="fas fa-moon"></i></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.min.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('reverb.apps.default.app_key') }}',
            wsHost: '{{ config('reverb.apps.default.host') }}',
            wsPort: '{{ config('reverb.apps.default.port') }}',
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });
    </script>
    @livewireScripts
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var currencySwitcher = document.getElementById('currencySwitcher');
        if (!currencySwitcher) return;
        var saved = localStorage.getItem('preferred_currency');
        if (saved) currencySwitcher.value = saved;
        var rates = { USD: 1 };
        fetch('{{ route("currency.rates", ["locale" => $currentLocale]) }}')
            .then(function(r) { return r.json(); })
            .then(function(data) { rates = data; applyCurrency(currencySwitcher.value); })
            .catch(function() { applyCurrency(currencySwitcher.value); });
        currencySwitcher.addEventListener('change', function() {
            localStorage.setItem('preferred_currency', this.value);
            applyCurrency(this.value);
        });
        function applyCurrency(currency) {
            var rate = rates[currency] || 1;
            var symbol = { USD: '$', EUR: '€', GBP: '£', TRY: '₺' }[currency] || '$';
            document.querySelectorAll('[data-amount]').forEach(function(el) {
                var usd = parseFloat(el.getAttribute('data-amount'));
                if (!isNaN(usd)) {
                    var converted = (currency === 'USD') ? usd : (usd * rate).toFixed(2);
                    el.textContent = symbol + Number(converted).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                }
            });
        }
    });
    </script>
    @livewire('chat-widget')
    @stack('scripts')

    <div id="cookieConsent" style="position:fixed;bottom:0;left:0;right:0;background:var(--color-bg);border-top:1px solid var(--color-border);padding:14px 20px;z-index:9999;transform:translateY(100%);transition:transform 0.4s ease;{{ $isRtl ? 'text-align:right' : '' }}" role="alert">
        <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
            <p style="margin:0;font-size:0.85rem;color:var(--color-text);flex:1;min-width:200px">{{ __('common.cookie_desc') }}</p>
            <div style="display:flex;gap:8px;flex-shrink:0">
                <button onclick="localStorage.setItem('cookie_consent','accepted');document.getElementById('cookieConsent').style.transform='translateY(100%)'" style="padding:8px 20px;border:none;border-radius:6px;background:var(--color-primary);color:#fff;font-size:0.85rem;cursor:pointer">{{ __('common.cookie_accept') }}</button>
                <button onclick="localStorage.setItem('cookie_consent','declined');document.getElementById('cookieConsent').style.transform='translateY(100%)'" style="padding:8px 20px;border:1px solid var(--color-border);border-radius:6px;background:var(--color-bg);color:var(--color-text);font-size:0.85rem;cursor:pointer">{{ __('common.cookie_decline') }}</button>
                <a href="{{ route('pages.show', ['locale' => $currentLocale, 'slug' => 'privacy-policy']) }}" style="padding:8px 16px;font-size:0.85rem;color:var(--color-primary);text-decoration:underline;display:inline-flex;align-items:center">{{ __('common.cookie_more') }}</a>
            </div>
        </div>
    </div>
    <script>if(!localStorage.getItem('cookie_consent')){setTimeout(function(){document.getElementById('cookieConsent').style.transform='translateY(0)'},1000)}</script>
</body>
</html>
