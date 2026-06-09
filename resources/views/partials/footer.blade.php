@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp
<footer class="footer">
    <div class="container footer__inner">
        <div class="footer__brand">
            <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="logo logo--footer">
                <span class="logo__icon">🤝</span>
                <span class="logo__text">
                    <strong>{{ trans_field($s, 'site_name') }}</strong>
                    <small>{{ trans_field($s, 'tagline') }}</small>
                </span>
            </a>
            <p>{{ trans_field($s, 'footer_description') }}</p>
            <div class="footer__social">
                @if($s->facebook)<a href="{{ $s->facebook }}" target="_blank" rel="noopener"><i class="fab fa-facebook"></i></a>@endif
                @if($s->twitter)<a href="{{ $s->twitter }}" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>@endif
                @if($s->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $s->whatsapp) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>@endif
            </div>
        </div>
        <nav class="footer__nav">
            <h4>{{ __('home.quick_links') }}</h4>
            <a href="{{ route('home', ['locale' => $currentLocale]) }}">{{ __('common.nav_home') }}</a>
            <a href="{{ route('about.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_about') }}</a>
            <a href="{{ route('projects.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_projects') }}</a>
            <a href="{{ route('posts.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_news') }}</a>
            <a href="{{ route('stories.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_stories') }}</a>
            <a href="{{ route('home', ['locale' => $currentLocale]) }}#donate">{{ __('common.nav_donate') }}</a>
            <a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}">{{ __('volunteer.nav') }}</a>
            <a href="{{ route('donor.dashboard', ['locale' => $currentLocale]) }}">{{ __('donor.nav_dashboard') }}</a>
            <a href="{{ route('home', ['locale' => $currentLocale]) }}#contact">{{ __('common.nav_contact') }}</a>
            <a href="{{ route('transparency.index', ['locale' => $currentLocale]) }}">{{ __('common.transparency') }}</a>
        </nav>
        <div class="footer__newsletter">
            <h4>{{ __('common.subscribe_newsletter') }}</h4>
            <form action="{{ route('newsletter.store', ['locale' => $currentLocale]) }}" method="POST">
                @csrf
                <input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                <input type="email" name="email" placeholder="{{ __('common.newsletter_placeholder') }}" required>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="container">
            <p>{{ __('common.all_rights') }} &copy; {{ trans_field($s, 'site_name') }} {{ date('Y') }} — <a href="{{ route('pages.show', ['locale' => $currentLocale, 'slug' => 'privacy-policy']) }}" style="color:rgba(255,255,255,0.6);text-decoration:underline">{{ __('common.privacy_policy') }}</a></p>
        </div>
    </div>
</footer>
