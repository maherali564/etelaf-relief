<?php $s = $settings ?? \App\Models\SiteSetting::current(); ?>
<footer class="footer">
    <div class="footer__top">
        <div class="footer__inner">
            <div class="footer__brand">
                <a href="<?php echo e(route('home', ['locale' => $currentLocale])); ?>" class="footer__logo-link">
<?php $logoSrc = $s->logos[$currentLocale] ?? $s->logo ?? '/images/sahemlogo.svg'; ?>
                    <img src="<?php echo e($logoSrc && str_starts_with($logoSrc, 'http') ? $logoSrc : Storage::url($logoSrc)); ?>" alt="<?php echo e(trans_field($s, 'site_name') ?? 'Sahem'); ?>" class="footer__logo">
                </a>
                <p><?php echo e(trans_field($s, 'footer_description') ?? trans_field($s, 'tagline')); ?></p>
                <div class="footer__social">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->facebook): ?><a href="<?php echo e($s->facebook); ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->twitter): ?><a href="<?php echo e($s->twitter); ?>" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->whatsapp): ?><a href="https://wa.me/<?php echo e(preg_replace('/\D/', '', $s->whatsapp)); ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="footer__col">
                <h4><?php echo e(__('common.quick_links')); ?></h4>
                <ul class="footer__links">
                    <li><a href="<?php echo e(route('home', ['locale' => $currentLocale])); ?>"><?php echo e(__('common.nav_home')); ?></a></li>
                    <li><a href="<?php echo e(route('about.index', ['locale' => $currentLocale])); ?>"><?php echo e(__('common.nav_about')); ?></a></li>
                    <li><a href="<?php echo e(route('programs.index', ['locale' => $currentLocale])); ?>"><?php echo e(__('common.nav_programs')); ?></a></li>
                    <li><a href="<?php echo e(route('projects.index', ['locale' => $currentLocale])); ?>"><?php echo e(__('common.nav_projects')); ?></a></li>
                    <li><a href="<?php echo e(route('stories.index', ['locale' => $currentLocale])); ?>"><?php echo e(__('common.nav_stories')); ?></a></li>
                    <li><a href="<?php echo e(route('gallery.index', ['locale' => $currentLocale])); ?>"><?php echo e(__('common.nav_gallery')); ?></a></li>
                    <li><a href="<?php echo e(route('volunteer.register', ['locale' => $currentLocale])); ?>"><?php echo e(__('volunteer.nav')); ?></a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h4><?php echo e(__('common.contact_us')); ?></h4>
                <ul class="footer__contact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->phone): ?>
                    <li dir="ltr"><i class="fas fa-phone"></i> <a href="tel:<?php echo e($s->phone); ?>"><?php echo e($s->phone); ?></a></li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <li dir="ltr"><i class="fas fa-envelope"></i> <a href="mailto:<?php echo e($s->email ?? 'info@sahemrelief.org'); ?>"><?php echo e($s->email ?? 'info@sahemrelief.org'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="footer__bottom-inner">
            <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(trans_field($s, 'site_name') ?? 'ساهم للإغاثة و التنمية'); ?>. <?php echo e(__('common.all_rights')); ?></p>
            <div class="footer__bottom-links">
                <a href="<?php echo e(route('pages.show', ['locale' => $currentLocale, 'slug' => 'privacy-policy'])); ?>"><?php echo e(__('common.privacy_policy')); ?></a>
                <a href="<?php echo e(route('pages.show', ['locale' => $currentLocale, 'slug' => 'terms'])); ?>"><?php echo e(__('common.terms_of_use')); ?></a>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH D:\etelaf-relief-laravel\resources\views/partials/footer.blade.php ENDPATH**/ ?>