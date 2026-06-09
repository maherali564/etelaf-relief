@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp
<div class="top-bar">
    <div class="container top-bar__inner">
        <div class="top-bar__contact">
            @if($s->phone)<a href="tel:{{ preg_replace('/\s+/', '', $s->phone) }}"><i class="fas fa-phone"></i> {{ $s->phone }}</a>@endif
            @if($s->email)<a href="mailto:{{ $s->email }}"><i class="fas fa-envelope"></i> {{ $s->email }}</a>@endif
        </div>
        <div class="top-bar__currency">
            <select id="currencySwitcher" style="background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:4px;padding:2px 6px;font-size:0.8rem;font-family:var(--font-family);cursor:pointer">
                <option value="USD">USD $</option>
                <option value="EUR">EUR €</option>
                <option value="GBP">GBP £</option>
                <option value="TRY">TRY ₺</option>
            </select>
        </div>
        <div class="top-bar__langs">
            @foreach($supportedLocales as $loc)
                <a href="{{ locale_url($loc) }}" class="lang-switcher__link {{ $loc === $currentLocale ? 'active' : '' }}" title="{{ $localeLabels[$loc] ?? $loc }}">
                    <span class="lang-flag">{{ $localeFlags[$loc] ?? '' }}</span>
                    <span class="lang-name">{{ $localeLabels[$loc] ?? $loc }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
