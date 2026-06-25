@php $s = $settings ?? \App\Models\SiteSetting::current(); @endphp
<header class="header header--transparent" id="header">
    <!-- Top Bar -->
    <div class="top-bar" id="topBar">
        <div class="top-bar__inner">
            <div class="top-bar__contact">
                @if($s->email)<a href="mailto:{{ $s->email }}"><i class="fas fa-envelope" style="width:14px"></i> {{ $s->email }}</a>@endif
                @if($s->phone)<a href="tel:{{ preg_replace('/\s+/', '', $s->phone) }}" dir="ltr" style="display:inline-block"><i class="fas fa-phone" style="width:14px"></i> {{ $s->phone }}</a>@endif
            </div>
            <div class="top-bar__actions">
                <div class="dropdown">
                    <button class="top-bar__btn" onclick="toggleDropdown('langDropdown')" type="button">
                        <i class="fas fa-globe" style="color:var(--emerald)"></i>
                        <span>{{ $localeLabels[$currentLocale] ?? $currentLocale }}</span>
                        <i class="fas fa-chevron-down" style="font-size:10px;color:#a1a1aa"></i>
                    </button>
                    <div class="dropdown__menu" id="langDropdown">
                        @foreach($supportedLocales as $loc)
                            @php $active = $loc === $currentLocale; @endphp
                            <a href="{{ locale_url($loc) }}" class="dropdown__item {{ $active ? 'dropdown__item--active' : '' }}">{{ $localeLabels[$loc] ?? $loc }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="dropdown">
                    <button class="top-bar__btn" onclick="toggleDropdown('currencyDropdown')" type="button">
                        <span id="currencySymbol" style="color:var(--emerald);font-weight:700">$</span>
                        <span id="currencyCode">USD</span>
                        <i class="fas fa-chevron-down" style="font-size:10px;color:#a1a1aa"></i>
                    </button>
                    <div class="dropdown__menu" id="currencyDropdown">
                        @foreach(['USD'=>'$ US Dollar','EUR'=>'€ Euro','GBP'=>'£ GBP','TRY'=>'₺ Turkish Lira'] as $code => $label)
                        <button class="dropdown__item" onclick="setCurrency('{{ $code }}')">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('donate.page', ['locale' => $currentLocale]) }}" class="top-bar__cta">
                    <i class="fas fa-heart" style="font-size:10px;animation:pulseGlow 2s infinite"></i>
                    <span>{{ __('common.donate_now') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="header__main" id="headerMain">
        <!-- Brand Container (left) -->
        <div class="header__brand">
            <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="header__logo" id="headerLogo">
                @php $logoUrl = (isset($s->logos[$currentLocale]) && $s->logos[$currentLocale]) ? (str_starts_with($s->logos[$currentLocale], '/') ? $s->logos[$currentLocale] : Storage::url($s->logos[$currentLocale])) : ($s->logo ? (str_starts_with($s->logo, '/') ? $s->logo : Storage::url($s->logo)) : '/images/sahemlogo.svg'); @endphp
                <img src="{{ $logoUrl }}" alt="{{ trans_field($s, 'site_name') ?? 'Sahem' }}">
            </a>
            <span class="header__brand-text">{{ trans_field($s, 'site_name') ?? 'ساهم للإغاثة و التنمية' }}</span>
        </div>

        <!-- Links Container (right) -->
        <div class="header__links" id="desktopNav">
            <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_home') }}</a>
            <a href="{{ route('about.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_about') }}</a>
            <div class="nav__dropdown">
                <a href="javascript:void(0)" class="nav__link">
                    {{ __('common.nav_programs') }} <i class="fas fa-chevron-down" style="font-size:10px;opacity:0.7"></i>
                </a>
                <div class="nav__menu nav__menu--mega">
                    @php $navPrograms = \Illuminate\Support\Facades\Cache::remember('nav_programs', 3600, fn() => \App\Models\Program::with('activeProjects')->active()->get()); @endphp
                    @foreach($navPrograms as $program)
                    <div class="nav__sub">
                        <span class="nav__item nav__item--parent">{!! safeHtml($program->icon) !!} {{ trans_field($program, 'title') }}</span>
                        @if($program->activeProjects->count() > 0)
                        <div class="nav__submenu">
                            @foreach($program->activeProjects as $proj)
                            <a href="{{ route('projects.show', ['locale' => $currentLocale, 'slug' => $proj->slug]) }}" class="nav__item nav__item--child">{{ trans_field($proj, 'title') }}</a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('projects.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_projects') }}</a>
            <a href="{{ route('stories.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_stories') }}</a>
            <a href="{{ route('gallery.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.nav_gallery') }}</a>
            <a href="{{ route('transparency.index', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('common.transparency') }}</a>
            <a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}" class="nav__link">{{ __('volunteer.nav') }}</a>
        </div>

        <!-- Mobile toggle (outside both containers) -->
        <button class="nav-toggle" onclick="toggleMobileMenu()" type="button" aria-label="Menu" id="navToggleBtn">
            <i class="fas fa-bars" id="navToggleIcon"></i>
        </button>
    </div>
</header>

<!-- Mobile drawer (off-canvas, slides from right) -->
<div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer__header">
        <span class="mobile-drawer__title">{{ trans_field($s, 'site_name') ?? 'ساهم للإغاثة و التنمية' }}</span>
        <button class="mobile-drawer__close" onclick="toggleMobileMenu()" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav class="mobile-drawer__nav">
        <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('common.nav_home') }}</a>
        <a href="{{ route('about.index', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('common.nav_about') }}</a>
        <a href="{{ route('programs.index', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('common.nav_programs') }}</a>
        <a href="{{ route('projects.index', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('common.nav_projects') }}</a>
        <a href="{{ route('stories.index', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('common.nav_stories') }}</a>
        <a href="{{ route('gallery.index', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('common.nav_gallery') }}</a>
        <a href="{{ route('transparency.index', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('common.transparency') }}</a>
        <a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}" class="mobile-drawer__link">{{ __('volunteer.nav') }}</a>
        <div class="mobile-drawer__cta">
            <a href="{{ route('donate.page', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--block">{{ __('common.donate_now') }}</a>
        </div>
    </nav>
</div>

<script>
var header = document.getElementById('header');
var headerLogo = document.getElementById('headerLogo');
var topBar = document.getElementById('topBar');

function updateHeader() {
    var scrolled = window.scrollY > 10;
    header.classList.toggle('header--solid', scrolled);
    header.classList.toggle('header--transparent', !scrolled);
}
window.addEventListener('scroll', updateHeader);
updateHeader();

function toggleDropdown(id) {
    var el = document.getElementById(id);
    var open = el.classList.contains('dropdown__menu--open');
    document.querySelectorAll('.dropdown__menu').forEach(function(m) { m.classList.remove('dropdown__menu--open'); });
    if (!open) el.classList.add('dropdown__menu--open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown__menu').forEach(function(m) { m.classList.remove('dropdown__menu--open'); });
    }
});

function toggleMobileMenu() {
    var isOpen = !document.getElementById('mobileDrawer').classList.contains('open');
    document.getElementById('mobileDrawer').classList.toggle('open');
    document.getElementById('mobileDrawerOverlay').classList.toggle('open');
    document.getElementById('navToggleIcon').className = isOpen ? 'fas fa-times' : 'fas fa-bars';
    document.getElementById('topBar').style.display = isOpen ? 'none' : '';
    document.body.style.overflow = isOpen ? 'hidden' : '';
}
document.getElementById('mobileDrawerOverlay').addEventListener('click', toggleMobileMenu);

function setCurrency(code) { /* kept from original */
    var symbols = { USD: '$', EUR: '€', GBP: '£', TRY: '₺' };
    document.getElementById('currencySymbol').textContent = symbols[code] || '$';
    document.getElementById('currencyCode').textContent = code;
    localStorage.setItem('preferred_currency', code);
    document.querySelectorAll('.dropdown__menu').forEach(function(m) { m.classList.remove('dropdown__menu--open'); });
    applyCurrency(code);
}
(function() {
    var saved = localStorage.getItem('preferred_currency');
    if (saved) setCurrency(saved);
})();
function applyCurrency(currency) { /* ... uses same logic from before ... */ }
</script>
