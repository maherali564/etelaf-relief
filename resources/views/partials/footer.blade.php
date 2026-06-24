@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp
<footer class="footer">
    <div class="footer__top">
        <div class="footer__inner">
            <div class="footer__brand">
                <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="footer__logo-link">
@php $logoSrc = $s->logos[$currentLocale] ?? $s->logo ?? '/images/sahemlogo.svg'; @endphp
                    <img src="{{ $logoSrc && str_starts_with($logoSrc, 'http') ? $logoSrc : Storage::url($logoSrc) }}" alt="{{ trans_field($s, 'site_name') ?? 'Sahem' }}" class="footer__logo">
                </a>
                <p>{{ trans_field($s, 'footer_description') ?? trans_field($s, 'tagline') }}</p>
                <div class="footer__social">
                    @if($s->facebook)<a href="{{ $s->facebook }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>@endif
                    @if($s->twitter)<a href="{{ $s->twitter }}" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>@endif
                    @if($s->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $s->whatsapp) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>@endif
                </div>
            </div>
            <div class="footer__col">
                <h4>{{ __('common.quick_links') }}</h4>
                <ul class="footer__links">
                    <li><a href="{{ route('home', ['locale' => $currentLocale]) }}">{{ __('common.nav_home') }}</a></li>
                    <li><a href="{{ route('about.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_about') }}</a></li>
                    <li><a href="{{ route('programs.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_programs') }}</a></li>
                    <li><a href="{{ route('projects.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_projects') }}</a></li>
                    <li><a href="{{ route('stories.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_stories') }}</a></li>
                    <li><a href="{{ route('gallery.index', ['locale' => $currentLocale]) }}">{{ __('common.nav_gallery') }}</a></li>
                    <li><a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}">{{ __('volunteer.nav') }}</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h4>{{ __('common.contact_us') }}</h4>
                <ul class="footer__contact">
                    @if($s->phone)
                    <li dir="ltr"><i class="fas fa-phone"></i> <a href="tel:{{ $s->phone }}">{{ $s->phone }}</a></li>
                    @endif
                    <li dir="ltr"><i class="fas fa-envelope"></i> <a href="mailto:{{ $s->email ?? 'info@sahemrelief.org' }}">{{ $s->email ?? 'info@sahemrelief.org' }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="footer__bottom-inner">
            <p>&copy; {{ date('Y') }} {{ trans_field($s, 'site_name') ?? 'ساهم للإغاثة و التنمية' }}. {{ __('common.all_rights') }}</p>
            <div class="footer__bottom-links">
                <a href="{{ route('pages.show', ['locale' => $currentLocale, 'slug' => 'privacy-policy']) }}">{{ __('common.privacy_policy') }}</a>
                <a href="{{ route('pages.show', ['locale' => $currentLocale, 'slug' => 'terms']) }}">{{ __('common.terms_of_use') }}</a>
            </div>
        </div>
    </div>
</footer>
