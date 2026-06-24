<?php $__env->startSection('content'); ?>
<section class="section error-section">
    <div class="container error-container">
        <h1 class="error-code" style="color:var(--color-primary)">404</h1>
        <h2 class="error-title"><?php echo e(__('errors.not_found_title')); ?></h2>
        <p class="error-desc" style="color:var(--color-text-muted)"><?php echo e(__('errors.not_found_desc')); ?></p>
        <a href="<?php echo e(route('home', ['locale' => $currentLocale ?? app()->getLocale()])); ?>" class="btn btn--primary btn--lg"><?php echo e(__('errors.back_home')); ?></a>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\etelaf-relief-laravel\resources\views/errors/404.blade.php ENDPATH**/ ?>