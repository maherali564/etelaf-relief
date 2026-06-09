<?php $__env->startComponent('mail::message'); ?>
# <?php echo new \Illuminate\Support\EncodedHtmlString(__('common.newsletter_verify_title')); ?>


<?php echo new \Illuminate\Support\EncodedHtmlString(__('common.newsletter_verify_body')); ?>


<?php $__env->startComponent('mail::button', ['url' => $verifyUrl ?? route('newsletter.verify', ['token' => $subscriber->verify_token, 'locale' => app()->getLocale()])]); ?>
<?php echo new \Illuminate\Support\EncodedHtmlString(__('common.newsletter_verify_button')); ?>

<?php echo $__env->renderComponent(); ?>

<?php echo new \Illuminate\Support\EncodedHtmlString(__('common.newsletter_verify_ignore')); ?>


<?php echo new \Illuminate\Support\EncodedHtmlString(__('common.thanks')); ?>,<br>
<?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\Hp\Desktop\etelaf-relief-laravel\resources\views/emails/newsletter/verify.blade.php ENDPATH**/ ?>