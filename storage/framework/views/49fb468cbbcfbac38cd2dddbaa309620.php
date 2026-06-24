
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stories->isNotEmpty()): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag"><i class="fas fa-book-open"></i> <?php echo e(__('common.nav_stories')); ?></span>
            <h2 class="section-title"><?php echo e(__('common.nav_stories')); ?></h2>
            <p class="section-desc"><?php echo e(__('home.voices_waiting_desc')); ?></p>
        </div>
        <div class="stories__grid <?php echo e($stories->count() === 1 ? 'stories__grid--single' : ''); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $story): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="story-card card-hover">
                <div class="story-card__image" style="background-image: url('<?php echo e(asset('storage/'.$story->first_image)); ?>')"></div>
                <div class="story-card__body">
                    <h3><?php echo e(trans_field($story, 'title')); ?></h3>
                    <p class="story-card__meta"><?php echo e(trans_field($story, 'person_name')); ?><?php echo e(trans_field($story, 'age') ? ', '.trans_field($story, 'age').' '.__('common.age') : ''); ?><?php echo e(trans_field($story, 'location') ? ' | '.trans_field($story, 'location') : ''); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($story->goal_amount > 0 || ($story->raised_amount ?? 0) > 0): ?>
                    <div class="project-card__progress">
                        <div class="project-card__bar">
                            <div class="project-card__fill" style="width:<?php echo e($story->progressPercent()); ?>%"></div>
                        </div>
                        <div class="project-card__stats">
                            <span><?php echo e(number_format($story->raised_amount ?? 0)); ?> / <?php echo e(number_format($story->goal_amount)); ?></span>
                            <span><?php echo e($story->progressPercent()); ?>%</span>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="story-card__actions">
                        <a href="<?php echo e(route('stories.show', ['locale' => $currentLocale, 'id' => $story->id])); ?>" class="btn btn--primary btn--sm"><?php echo e(__('common.contribute')); ?></a>
                    </div>
                </div>
            </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH D:\etelaf-relief-laravel\resources\views/partials/home-content.blade.php ENDPATH**/ ?>