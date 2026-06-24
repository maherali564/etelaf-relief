<?php $__env->startSection('content'); ?>
<section class="hero-detailed">
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-project-diagram"></i> <?php echo e(__('home.projects_tag') ?? __('site.nav_projects')); ?></span>
        <h1 class="hero-detailed__title"><?php echo e(__('site.nav_projects')); ?></h1>
        <p class="hero-detailed__desc"><?php echo e(__('home.projects_desc') ?? ''); ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="projects__slider">
            <div class="projects__track <?php echo e($projects->count() === 1 ? 'projects__track--single' : ''); ?>" id="projectsTrack">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="project-card card-hover">
                <div class="project-card__image">
                    <img src="<?php echo e(asset('storage/'.($project->image ?? $project->images[0] ?? $project->first_image ?? 'default.jpg'))); ?>" alt="<?php echo e(trans_field($project, 'title')); ?>" loading="lazy">
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
                            <div class="project-card__fill" style="width:<?php echo e($project->progressPercent() ?? 0); ?>%"></div>
                        </div>
                        <div class="project-card__stats">
                            <span><?php echo e(number_format($project->raised_amount ?? 0)); ?> / <?php echo e(number_format($project->goal_amount)); ?></span>
                            <span><?php echo e($project->progressPercent() ?? 0); ?>%</span>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="project-card__actions">
                        <a href="<?php echo e(route('projects.show', ['locale' => $currentLocale, 'slug' => $project->slug])); ?>" class="btn btn--primary btn--sm"><?php echo e(__('common.donate_now')); ?></a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p><?php echo e(__('common.no_results')); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\etelaf-relief-laravel\resources\views/projects/index.blade.php ENDPATH**/ ?>