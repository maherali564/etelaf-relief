<?php $s = $settings ?? \App\Models\SiteSetting::current(); ?>
<footer class="footer">
    <div class="footer__top">
        <div class="footer__inner">
            <div class="footer__col footer__col--brand">
                <a href="<?php echo e(route('home', ['locale' => $currentLocale])); ?>" class="footer__logo-link">
<?php $logoSrc = $s->logos[$currentLocale] ?? $s->logo ?? '/images/sahemlogo.svg'; $logoUrl = $logoSrc && (str_starts_with($logoSrc, '/') || str_starts_with($logoSrc, 'http')) ? $logoSrc : Storage::url($logoSrc); ?>
                    <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e(trans_field($s, 'site_name') ?? 'Sahem'); ?>" class="footer__logo">
                </a>
                <p class="footer__mission"><?php echo e(trans_field($s, 'footer_description') ?? trans_field($s, 'tagline') ?? 'نعمل من أجل تخفيف المعاناة الإنسانية وتقديم الدعم الإغاثي للأسر المتضررة في قطاع غزة.'); ?></p>
                <div class="footer__social">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->whatsapp): ?><a href="https://wa.me/<?php echo e(preg_replace('/\D/', '', $s->whatsapp)); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->twitter): ?><a href="<?php echo e($s->twitter); ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->facebook): ?><a href="<?php echo e($s->facebook); ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="footer__col footer__col--links">
                <h4><?php echo e(__('common.quick_links')); ?></h4>
                <ul class="footer__links">
                    <li><a href="<?php echo e(route('home', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('common.nav_home')); ?></a></li>
                    <li><a href="<?php echo e(route('about.index', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('common.nav_about')); ?></a></li>
                    <li><a href="<?php echo e(route('programs.index', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('common.nav_programs')); ?></a></li>
                    <li><a href="<?php echo e(route('projects.index', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('common.nav_projects')); ?></a></li>
                    <li><a href="<?php echo e(route('stories.index', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('common.nav_stories')); ?></a></li>
                    <li><a href="<?php echo e(route('gallery.index', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('common.nav_gallery')); ?></a></li>
                    <li><a href="<?php echo e(route('transparency.index', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('common.transparency')); ?></a></li>
                    <li><a href="<?php echo e(route('volunteer.register', ['locale' => $currentLocale])); ?>"><i class="fas fa-chevron-left"></i> <?php echo e(__('volunteer.nav')); ?></a></li>
                </ul>
            </div>
            <div class="footer__col footer__col--contact">
                <h4><?php echo e(__('common.contact_us')); ?></h4>
                <ul class="footer__contact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->phone): ?>
                    <li><i class="fas fa-phone"></i> <a href="tel:<?php echo e($s->phone); ?>"><?php echo e($s->phone); ?></a></li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <li><i class="fas fa-envelope"></i> <a href="mailto:<?php echo e($s->email ?: 'info@sahem.org'); ?>"><?php echo e($s->email ?: 'info@sahem.org'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="footer__bottom-inner">
            <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(trans_field($s, 'site_name') ?? 'ساهم للإغاثة والتنمية'); ?>. <?php echo e(__('common.all_rights')); ?></p>
        </div>
    </div>
</footer><?php /**PATH D:\etelaf-relief-laravel\resources\views/partials/footer.blade.php ENDPATH**/ ?>