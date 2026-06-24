<?php $__env->startSection('content'); ?>

<section class="section page-header">
    <div class="container">
        <span class="section-tag"><i class="fas fa-info-circle"></i> <?php echo e(__('common.about_us')); ?></span>
        <h1 class="section-title"><?php echo e(__('site.about_title')); ?></h1>
        <p><?php echo e(__('site.about_desc')); ?></p>
    </div>
</section>

<div class="about-page pb-24">
    
    <section class="section">
        <div class="container">
            <div class="about-story-grid">
                <div class="about-story-content">
                    <span class="section-tag"><i class="fas fa-book-open"></i> <?php echo e(__('site.our_story')); ?></span>
                    <h2 class="about-story-title"><?php echo e(__('site.story_title')); ?></h2>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($settings->about_content)): ?>
                    <div class="about-story-quote">
                        <p><?php echo e($settings->about_content); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="about-story-text">
                        <p><?php echo e(__('site.story_p1')); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(__('site.story_p2') && __('site.story_p2') !== 'site.story_p2'): ?>
                        <p><?php echo e(__('site.story_p2')); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($aboutFeatures) > 0): ?>
                    <div class="about-features-grid">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $aboutFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span><?php echo e($feature); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="about-features-grid">
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span><?php echo e(__('site.value_transparency_title')); ?></span>
                        </div>
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span><?php echo e(__('site.value_integrity_title')); ?></span>
                        </div>
                        <div class="about-feature-badge">
                            <i class="fas fa-check-circle about-feature-badge__icon"></i>
                            <span><?php echo e(__('site.value_impact_title')); ?></span>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="about-story-visual">
                    <div class="about-story-visual__glow"></div>
                    <div class="about-story-visual__frame">
                        <img
                            src="<?php echo e($settings->about_image ? Storage::url($settings->about_image) : asset('images/about-hero.webp')); ?>"
                            alt="<?php echo e(__('common.about_us')); ?>"
                            class="about-story-visual__img"
                        />
                        <div class="about-story-visual__badge">
                            <div class="about-story-visual__badge-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="about-story-visual__badge-text">
                                <h4><?php echo e(app()->getLocale() === 'ar' ? 'عطاء ممتد ومستمر' : 'Continuous Giving'); ?></h4>
                                <p><?php echo e(app()->getLocale() === 'ar' ? 'مشاريعنا تخدم الإنسان أينما كان' : 'Our projects serve humanity globally'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="section">
        <div class="container">
            <div class="about-mv-grid">
                <div class="about-mv-card about-mv-card--mission">
                    <div class="about-mv-card__glow"></div>
                    <div class="about-mv-card__body">
                        <div class="about-mv-card__icon about-mv-card__icon--emerald">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3><?php echo e(__('site.mission_title')); ?></h3>
                        <p><?php echo e(__('site.mission_desc')); ?></p>
                    </div>
                </div>
                <div class="about-mv-card about-mv-card--vision">
                    <div class="about-mv-card__glow"></div>
                    <div class="about-mv-card__body">
                        <div class="about-mv-card__icon about-mv-card__icon--teal">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3><?php echo e(__('site.vision_title')); ?></h3>
                        <p><?php echo e(__('site.vision_desc')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="section">
        <div class="container">
            <div class="section-header section-header--center">
                <span class="section-tag"><i class="fas fa-star"></i> <?php echo e(__('site.core_values')); ?></span>
                <h2 class="section-title"><?php echo e(__('site.values_title')); ?></h2>
            </div>
            <div class="about-values-grid">
                <?php
                    $values = [
                        'transparency' => ['icon' => 'fa-shield-halved', 'color' => 'emerald'],
                        'integrity' => ['icon' => 'fa-bolt', 'color' => 'teal'],
                        'impact' => ['icon' => 'fa-hand-holding-heart', 'color' => 'red'],
                        'compassion' => ['icon' => 'fa-seedling', 'color' => 'indigo'],
                    ];
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="about-value-card card-hover">
                    <div class="about-value-card__icon about-value-card__icon--<?php echo e($val['color']); ?>">
                        <i class="fas <?php echo e($val['icon']); ?>"></i>
                    </div>
                    <h4><?php echo e(__("site.value_{$key}_title")); ?></h4>
                    <p><?php echo e(__("site.value_{$key}_desc")); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="section">
        <div class="container">
            <div class="about-cta">
                <div class="about-cta__glow"></div>
                <div class="about-cta__body">
                    <h3 class="about-cta__title"><?php echo e(__('site.cta_title')); ?></h3>
                    <p class="about-cta__desc"><?php echo e(__('site.cta_desc')); ?></p>
                    <div class="about-cta__actions">
                        <a href="<?php echo e(route('donate.page', ['locale' => $currentLocale])); ?>" class="btn btn--primary btn--lg about-cta__btn about-cta__btn--primary">
                            <span><?php echo e(__('common.donate_now')); ?></span>
                            <i class="fas fa-arrow-<?php echo e(app()->getLocale() === 'ar' ? 'left' : 'right'); ?> about-cta__arrow"></i>
                        </a>
                        <a href="<?php echo e(route('volunteer.register', ['locale' => $currentLocale])); ?>" class="btn btn--lg about-cta__btn about-cta__btn--outline">
                            <i class="fas fa-hands-helping"></i>
                            <span><?php echo e(__('volunteer.nav')); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\etelaf-relief-laravel\resources\views/pages/about.blade.php ENDPATH**/ ?>