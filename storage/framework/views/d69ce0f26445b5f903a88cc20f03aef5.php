<?php $s = $settings ?? \App\Models\SiteSetting::current(); ?>
<header class="header header--transparent" id="header">
    <!-- Top Bar -->
    <div class="top-bar" id="topBar">
        <div class="top-bar__inner">
            <div class="top-bar__contact">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->email): ?><a href="mailto:<?php echo e($s->email); ?>"><i class="fas fa-envelope" style="width:14px"></i> <?php echo e($s->email); ?></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->phone): ?><a href="tel:<?php echo e(preg_replace('/\s+/', '', $s->phone)); ?>" dir="ltr" style="display:inline-block"><i class="fas fa-phone" style="width:14px"></i> <?php echo e($s->phone); ?></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="top-bar__actions">
                <div class="dropdown">
                    <button class="top-bar__btn" onclick="toggleDropdown('langDropdown')" type="button">
                        <i class="fas fa-globe" style="color:var(--emerald)"></i>
                        <span><?php echo e($localeLabels[$currentLocale] ?? $currentLocale); ?></span>
                        <i class="fas fa-chevron-down" style="font-size:10px;color:#a1a1aa"></i>
                    </button>
                    <div class="dropdown__menu" id="langDropdown">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supportedLocales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $active = $loc === $currentLocale; ?>
                            <a href="<?php echo e(locale_url($loc)); ?>" class="dropdown__item <?php echo e($active ? 'dropdown__item--active' : ''); ?>"><?php echo e($localeLabels[$loc] ?? $loc); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="top-bar__btn" onclick="toggleDropdown('currencyDropdown')" type="button">
                        <span id="currencySymbol" style="color:var(--emerald);font-weight:700">$</span>
                        <span id="currencyCode">USD</span>
                        <i class="fas fa-chevron-down" style="font-size:10px;color:#a1a1aa"></i>
                    </button>
                    <div class="dropdown__menu" id="currencyDropdown">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['USD'=>'$ US Dollar','EUR'=>'€ Euro','GBP'=>'£ GBP','TRY'=>'₺ Turkish Lira']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button class="dropdown__item" onclick="setCurrency('<?php echo e($code); ?>')"><?php echo e($label); ?></button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <a href="<?php echo e(route('donate.page', ['locale' => $currentLocale])); ?>" class="top-bar__cta">
                    <i class="fas fa-heart" style="font-size:10px;animation:pulseGlow 2s infinite"></i>
                    <span><?php echo e(__('common.donate_now')); ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="header__main" id="headerMain">
        <!-- Logo + Site Name -->
        <a href="<?php echo e(route('home', ['locale' => $currentLocale])); ?>" class="header__logo" id="headerLogo">
            <?php $logoUrl = (isset($s->logos[$currentLocale]) && $s->logos[$currentLocale]) ? (str_starts_with($s->logos[$currentLocale], '/') ? $s->logos[$currentLocale] : Storage::url($s->logos[$currentLocale])) : ($s->logo ? (str_starts_with($s->logo, '/') ? $s->logo : Storage::url($s->logo)) : '/images/sahemlogo.svg'); ?>
            <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e(trans_field($s, 'site_name') ?? 'Sahem'); ?>">
            <span class="header__brand-text"><?php echo e(trans_field($s, 'site_name') ?? 'ساهم للإغاثة و التنمية'); ?></span>
        </a>

        <!-- Desktop Nav -->
        <nav class="nav" id="desktopNav">
            <a href="<?php echo e(route('home', ['locale' => $currentLocale])); ?>" class="nav__link"><?php echo e(__('common.nav_home')); ?></a>
            <a href="<?php echo e(route('about.index', ['locale' => $currentLocale])); ?>" class="nav__link"><?php echo e(__('common.nav_about')); ?></a>
            <div class="nav__dropdown">
                <a href="javascript:void(0)" class="nav__link">
                    <?php echo e(__('common.nav_programs')); ?> <i class="fas fa-chevron-down" style="font-size:10px;opacity:0.7"></i>
                </a>
                <div class="nav__menu nav__menu--mega">
                    <?php $navPrograms = \Illuminate\Support\Facades\Cache::remember('nav_programs', 3600, fn() => \App\Models\Program::with('activeProjects')->active()->get()); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navPrograms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="nav__sub">
                        <span class="nav__item nav__item--parent"><?php echo safeHtml($program->icon); ?> <?php echo e(trans_field($program, 'title')); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($program->activeProjects->count() > 0): ?>
                        <div class="nav__submenu">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $program->activeProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('projects.show', ['locale' => $currentLocale, 'slug' => $proj->slug])); ?>" class="nav__item nav__item--child"><?php echo e(trans_field($proj, 'title')); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <a href="<?php echo e(route('projects.index', ['locale' => $currentLocale])); ?>" class="nav__link"><?php echo e(__('common.nav_projects')); ?></a>
            <a href="<?php echo e(route('stories.index', ['locale' => $currentLocale])); ?>" class="nav__link"><?php echo e(__('common.nav_stories')); ?></a>
            <a href="<?php echo e(route('gallery.index', ['locale' => $currentLocale])); ?>" class="nav__link"><?php echo e(__('common.nav_gallery')); ?></a>
            <a href="<?php echo e(route('transparency.index', ['locale' => $currentLocale])); ?>" class="nav__link"><?php echo e(__('common.transparency')); ?></a>
            <a href="<?php echo e(route('volunteer.register', ['locale' => $currentLocale])); ?>" class="nav__link"><?php echo e(__('volunteer.nav')); ?></a>

            <!-- Mobile toggle -->
            <button class="nav__mobile-toggle" onclick="toggleMobileMenu()" type="button" aria-label="Menu">
                <i class="fas fa-bars" style="font-size:1.3rem"></i>
            </button>
        </nav>
    </div>

    <!-- Mobile Menu -->
    <div class="nav__mobile" id="mobileMenu">
        <div class="nav__mobile-panel">
            <button class="nav__mobile-close" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
            <a href="<?php echo e(route('home', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('common.nav_home')); ?></a>
            <a href="<?php echo e(route('about.index', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('common.nav_about')); ?></a>
            <a href="<?php echo e(route('programs.index', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('common.nav_programs')); ?></a>
            <a href="<?php echo e(route('projects.index', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('common.nav_projects')); ?></a>
            <a href="<?php echo e(route('stories.index', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('common.nav_stories')); ?></a>
            <a href="<?php echo e(route('gallery.index', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('common.nav_gallery')); ?></a>
            <a href="<?php echo e(route('transparency.index', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('common.transparency')); ?></a>
            <a href="<?php echo e(route('volunteer.register', ['locale' => $currentLocale])); ?>" class="nav__mobile-link"><?php echo e(__('volunteer.nav')); ?></a>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #e4e4e7">
                <a href="<?php echo e(route('donate.page', ['locale' => $currentLocale])); ?>" class="btn btn--primary btn--block"><?php echo e(__('common.donate_now')); ?></a>
            </div>
        </div>
    </div>
</header>

<script>
var header = document.getElementById('header');
var headerLogo = document.getElementById('headerLogo');
var headerBrand = document.getElementById('headerBrand');
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
    var menu = document.getElementById('mobileMenu');
    menu.classList.toggle('nav__mobile--open');
}

function setCurrency(code) {
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
<?php /**PATH D:\etelaf-relief-laravel\resources\views/partials/header.blade.php ENDPATH**/ ?>