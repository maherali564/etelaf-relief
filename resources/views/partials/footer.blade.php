@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp
<footer class="footer">
    <div class="footer__top">
        <div class="footer__inner">
            <div class="footer__col footer__col--brand">
                <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="footer__logo-link">
@php $logoSrc = $s->logos[$currentLocale] ?? $s->logo ?? '/images/sahemlogo.svg'; $logoUrl = $logoSrc && (str_starts_with($logoSrc, '/') || str_starts_with($logoSrc, 'http')) ? $logoSrc : Storage::url($logoSrc); @endphp
                    <img src="{{ $logoUrl }}" alt="{{ trans_field($s, 'site_name') ?? 'Sahem' }}" class="footer__logo">
                </a>
                <p class="footer__mission">{{ trans_field($s, 'footer_description') ?? trans_field($s, 'tagline') ?? 'نعمل من أجل تخفيف المعاناة الإنسانية وتقديم الدعم الإغاثي للأسر المتضررة في قطاع غزة.' }}</p>
                <div class="footer__social">
                    @if($s->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $s->whatsapp) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>@endif
                    @if($s->twitter)<a href="{{ $s->twitter }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>@endif
                    @if($s->facebook)<a href="{{ $s->facebook }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>@endif
                </div>
            </div>
            <div class="footer__col footer__col--links">
                <h4>{{ __('common.quick_links') }}</h4>
                <ul class="footer__links">
                    <li><a href="{{ route('home', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('common.nav_home') }}</a></li>
                    <li><a href="{{ route('about.index', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('common.nav_about') }}</a></li>
                    <li><a href="{{ route('programs.index', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('common.nav_programs') }}</a></li>
                    <li><a href="{{ route('projects.index', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('common.nav_projects') }}</a></li>
                    <li><a href="{{ route('stories.index', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('common.nav_stories') }}</a></li>
                    <li><a href="{{ route('gallery.index', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('common.nav_gallery') }}</a></li>
                    <li><a href="{{ route('transparency.index', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('common.transparency') }}</a></li>
                    <li><a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}"><i class="fas fa-chevron-left"></i> {{ __('volunteer.nav') }}</a></li>
                </ul>
            </div>
            <div class="footer__col footer__col--contact">
                <h4>{{ __('common.contact_us') }}</h4>
                <ul class="footer__contact">
                    @if($s->phone)
                    <li><i class="fas fa-phone"></i> <a href="tel:{{ $s->phone }}">{{ $s->phone }}</a></li>
                    @endif
                    <li><i class="fas fa-envelope"></i> <a href="mailto:{{ $s->email ?: 'info@sahem.org' }}">{{ $s->email ?: 'info@sahem.org' }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="footer__bottom-inner">
            <p>&copy; {{ date('Y') }} {{ trans_field($s, 'site_name') ?? 'ساهم للإغاثة والتنمية' }}. {{ __('common.all_rights') }}</p>
        </div>
    </div>
</footer>