<?php $__env->startSection('content'); ?>
<section class="hero-detailed">
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-book-open"></i> <?php echo e(__('home.stories_tag') ?? __('common.nav_stories')); ?></span>
        <h1 class="hero-detailed__title"><?php echo e(__('common.nav_stories')); ?></h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stories__grid <?php echo e($stories->count() === 1 ? 'stories__grid--single' : ''); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $story): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="story-card card-hover">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($story->first_image): ?>
                <div class="story-card__image" style="background-image: url('<?php echo e(asset('storage/'.$story->first_image)); ?>')"></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="story-card__body">
                    <h3><?php echo e(trans_field($story, 'title')); ?></h3>
                    <p class="story-card__meta"><?php echo e(trans_field($story, 'person_name')); ?><?php echo e(trans_field($story, 'age') ? ', '.trans_field($story, 'age').' '.__('common.age') : ''); ?><?php echo e(trans_field($story, 'location') ? ' | '.trans_field($story, 'location') : ''); ?></p>
                    <?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['raised' => $story->raised_amount ?? 0,'goal' => $story->goal_amount,'label' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['raised' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($story->raised_amount ?? 0),'goal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($story->goal_amount),'label' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $attributes = $__attributesOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__attributesOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $component = $__componentOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__componentOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
                    <div class="story-card__actions">
                        <a href="<?php echo e(route('stories.show', ['locale' => $currentLocale, 'id' => $story->id])); ?>" class="btn btn--primary btn--sm"><?php echo e(__('common.contribute')); ?></a>
                    </div>
                </div>
            </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p><?php echo e(__('common.no_results')); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\etelaf-relief-laravel\resources\views/stories/index.blade.php ENDPATH**/ ?>