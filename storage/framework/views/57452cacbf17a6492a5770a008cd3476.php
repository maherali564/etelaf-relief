<?php $s = $settings; ?>
<?php
    $dictAr = ['eyebrow'=>__('home.eyebrow'),'title'=>'حين تساهم، يصل <em>أثرك</em> إلى حيث الحاجة أكبر','lead'=>__('home.hero_lead'),'cta_donate'=>__('common.donate_now'),'cta_explore'=>__('home.explore_work')];
    $dictEn = ['eyebrow'=>'','title'=>'When you give, your <em>impact</em> reaches where it\'s needed most','lead'=>'','cta_donate'=>'Donate Now','cta_explore'=>'Explore Our Work'];
?>

<?php $__env->startSection('content'); ?>

<div class="overflow-hidden">

<!-- ═══════════ HERO ═══════════ -->
<section class="hero" dir="<?php echo e($currentLocale === 'ar' ? 'rtl' : 'ltr'); ?>">
    <div class="hero__pattern" aria-hidden="true"></div>
    <div class="hero__inner">
        <div class="hero__content">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentLocale === 'ar' && !empty($urgentNote)): ?>
            <div class="hero__note"><i class="fas fa-circle" style="font-size:6px;color:var(--gold)"></i> <?php echo e($urgentNote); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <h1 class="hero__title"><?php echo $currentLocale === 'ar' ? $dictAr['title'] : $dictEn['title']; ?></h1>
            <p class="hero__desc"><?php echo e($currentLocale === 'ar' ? $dictAr['lead'] : ($heroSubtitle ?? __('home.hero_lead'))); ?></p>
            <div class="hero__actions">
                <a href="<?php echo e(route('donate.page', ['locale' => $currentLocale])); ?>" class="btn btn--primary"><i class="fas fa-heart"></i> <?php echo e($currentLocale === 'ar' ? $dictAr['cta_donate'] : $dictEn['cta_donate']); ?></a>
                <a href="<?php echo e(route('projects.index', ['locale' => $currentLocale])); ?>" class="btn btn--outline"><?php echo e($currentLocale === 'ar' ? $dictAr['cta_explore'] : $dictEn['cta_explore']); ?></a>
            </div>
        </div>
        <div class="hero__map">
            <?php echo $__env->make('partials.hero-map', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</section>

<!-- ═══════════ UNIFIED STATS ═══════════ -->
<?php $allStats = isset($statistics['humanitarian']) && $statistics['humanitarian']->isNotEmpty() ? $statistics['humanitarian']->concat($statistics['achievements'] ?? collect()) : ($statistics['achievements'] ?? collect()); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($allStats)): ?>
<section class="stats-exec">
    <div class="stats-exec__grid" aria-hidden="true"><svg width="100%" height="100%"><defs><pattern id="sg" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0L0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100%" height="100%" fill="url(#sg)"/></svg></div>
    <div class="stats-exec__glow1" aria-hidden="true"></div>
    <div class="stats-exec__glow2" aria-hidden="true"></div>
    <div class="stats-exec__accent" aria-hidden="true"></div>
    <div class="stats-exec__inner">
        <div class="section-header">
            <div class="section-tag" style="background:rgba(99,102,241,0.1);border-color:rgba(99,102,241,0.2);color:#818cf8">
                <i class="fas fa-hand-holding-heart"></i> <?php echo e(__('home.analytics_tag')); ?>

            </div>
            <h2 class="section-title" style="color:#fff"><?php echo e(__('home.analytics_title')); ?></h2>
            <p class="section-desc" style="color:#a1a1aa"><?php echo e(__('home.analytics_desc')); ?></p>
        </div>
        <div class="stats-exec__grid-cards">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="stat-exec-card animate-fadeInUp delay-<?php echo e($loop->index % 6 * 100 + 100); ?>">
                <div class="stat-exec-card__glow" aria-hidden="true"></div>
                <div class="stat-exec-card__inner">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($stat['icon'])): ?><div class="stat-exec-card__icon"><i class="fas fa-<?php echo e($stat['icon']); ?>"></i></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="stat-exec-card__value"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($stat['prefix'])): ?><small><?php echo e($stat['prefix']); ?></small><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php echo e($stat['value']); ?></div>
                    <div class="stat-exec-card__bar" aria-hidden="true"></div>
                    <div class="stat-exec-card__label"><?php echo e($stat['label']); ?></div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<!-- ═══════════ PROJECTS (only if data exists) ═══════════ -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($projects) && $projects->isNotEmpty()): ?>
<section class="section" id="work">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-hands-helping"></i> <?php echo e(__('home.projects_tag')); ?></div>
            <h2 class="section-title"><?php echo e(__('home.projects_title')); ?></h2>
            <p class="section-desc"><?php echo e(__('home.projects_desc')); ?></p>
        </div>
        <div class="projects__slider">
            <div class="projects__track <?php echo e($projects->count() === 1 ? 'projects__track--single' : ''); ?>" id="projectsTrack">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="project-card card-hover">
                    <div class="project-card__image">
                        <img src="<?php echo e(asset('storage/'.($project->image ?? $project->images[0] ?? 'default.jpg'))); ?>" alt="<?php echo e(trans_field($project, 'title')); ?>" loading="lazy">
                    </div>
                    <div class="project-card__body">
                        <h3><?php echo e(trans_field($project, 'title')); ?></h3>
                        <div class="project-card__meta" style="font-size:0.78rem;color:#64748b;margin-bottom:6px;display:flex;gap:10px;flex-wrap:wrap;">
                            <span><i class="fas fa-calendar-alt"></i> <?php echo e($project->created_at ? $project->created_at->format('Y-m-d') : '—'); ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo e(trans_field($project, 'location') ?? __('common.not_specified')); ?></span>
                        </div>
                        <p><?php echo e(Str::limit(trans_field($project, 'description') ?? trans_field($project, 'content'), 100)); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($project->goal_amount ?? 0) > 0 || ($project->raised_amount ?? 0) > 0): ?>
                        <div class="project-card__progress">
                            <div class="project-card__bar">
                                <div class="project-card__fill" style="width:<?php echo e($project->progressPercent()); ?>%"></div>
                            </div>
                            <div class="project-card__stats">
                                <span><?php echo e(number_format($project->raised_amount ?? 0)); ?> / <?php echo e(number_format($project->goal_amount)); ?></span>
                                <span><?php echo e($project->progressPercent()); ?>%</span>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="project-card__actions">
                            <a href="<?php echo e(route('projects.show', ['locale' => $currentLocale, 'slug' => $project->slug])); ?>" class="btn btn--primary btn--sm"><?php echo e(__('common.donate_now')); ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<!-- ═══════════ PROJECTS GRID ═══════════ -->
<?php echo $__env->make('partials.home-content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- ═══════════ VOLUNTEER + CONTACT ═══════════ -->
<?php echo $__env->make('partials.home-bottom', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('head'); ?>
<style>
.projects__track--single { justify-content: center; }
.stories__grid--single { display: flex; justify-content: center; }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });
document.querySelectorAll('.stat-exec-card, .project-card').forEach(function(el) {
    observer.observe(el);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\etelaf-relief-laravel\resources\views/home.blade.php ENDPATH**/ ?>