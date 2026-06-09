@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp
<header class="header" id="header">
    <div class="container header__inner">
        <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="logo">
            @php
                $logoSrc = null;
                if ($s->logos && is_array($s->logos) && isset($s->logos[$currentLocale])) {
                    $logoSrc = $s->logos[$currentLocale];
                } elseif ($s->logo) {
                    $logoSrc = $s->logo;
                }
            @endphp
            @if($logoSrc)
                <img loading="lazy" src="{{ asset('storage/'.$logoSrc) }}" alt="" class="logo__img">
            @else
                <span class="logo__icon" aria-hidden="true">🤝</span>
            @endif
            <span class="logo__text">
                <strong>{{ trans_field($s, 'site_name') }}</strong>
                <small>{{ trans_field($s, 'tagline') }}</small>
            </span>
        </a>
        <button class="nav-toggle" type="button" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="nav" id="nav">
            <ul class="nav__list">
                <li><a href="{{ route('home', ['locale' => $currentLocale]) }}#home" class="nav__link">{{ __('common.nav_home') }}</a></li>
                <li><a href="{{ route('about.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_about') }}</a></li>
                <li><a href="{{ route('projects.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_projects') }}</a></li>
                <li><a href="{{ route('posts.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_news') }}</a></li>
                <li><a href="{{ route('stories.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_stories') }}</a></li>
                <li><a href="{{ route('donor.wall', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_donate') }}</a></li>
                <li><a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('volunteer.nav') }}</a></li>
                @auth('donor')
                <li><a href="{{ route('donor.dashboard', ['locale' => $currentLocale]) }}" class="nav__link"><i class="fas fa-user"></i> {{ __('donor.nav_dashboard') }}</a></li>
                @else
                <li><a href="{{ route('donor.login', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('donor.nav_login') }}</a></li>
                @endauth
                <li><a href="{{ route('home', ['locale' => $currentLocale]) }}#contact" class="nav__link">{{ __('common.nav_contact') }}</a></li>
                <li><a href="{{ route('transparency.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.transparency') }}</a></li>
            </ul>
            <a href="{{ route('home', ['locale' => $currentLocale]) }}#donate" class="btn btn--primary btn--sm nav__cta">{{ __('common.donate_now') }}</a>
        </nav>
    </div>
</header>
