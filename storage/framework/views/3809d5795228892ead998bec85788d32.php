<!DOCTYPE html>
<html dir="<?php echo e($donation->locale === 'ar' ? 'rtl' : 'ltr'); ?>" lang="<?php echo e($donation->locale ?? 'ar'); ?>">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f5f5; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1)">
        <tr><td style="background: linear-gradient(135deg, #059669, #047857); padding: 30px; text-align: center;">
            <h1 style="color: #fff; margin: 0; font-size: 24px;"><?php echo e(__('certificate.email_heading', [], $donation->locale ?? 'ar')); ?></h1>
        </td></tr>
        <tr><td style="padding: 30px;">
            <p style="font-size: 18px; color: #1e293b;"><?php echo e(__('certificate.email_greeting', ['name' => $donation->donor_name], $donation->locale ?? 'ar')); ?></p>
            <p style="color: #64748b; line-height: 1.8;"><?php echo e(__('certificate.email_body', [], $donation->locale ?? 'ar')); ?></p>
            <div style="text-align:center;margin:30px 0;">
                <div style="display:inline-block;background:#f0fdf4;border:2px solid #059669;border-radius:16px;padding:20px 40px;">
                    <p style="color:#64748b;font-size:12px;margin:0 0 4px;"><?php echo e(__('certificate.email_amount', [], $donation->locale ?? 'ar')); ?></p>
                    <p style="font-size:28px;font-weight:bold;color:#059669;margin:0;">$<?php echo e(number_format($donation->amount, 2)); ?></p>
                </div>
            </div>
            <p style="color: #64748b; line-height: 1.8;"><?php echo e(__('certificate.email_attachment', [], $donation->locale ?? 'ar')); ?></p>
            <p style="color: #64748b; line-height: 1.8;"><?php echo e(__('certificate.email_verify', [], $donation->locale ?? 'ar')); ?><br>
            <a href="<?php echo e(url('/verify/donation/' . $donation->id)); ?>" style="color:#059669;"><?php echo e(url('/verify/donation/' . $donation->id)); ?></a></p>
        </td></tr>
        <tr><td style="background:#f8fafc;padding:20px;text-align:center;color:#94a3b8;font-size:12px;">
            &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. <?php echo e(__('common.all_rights_reserved', [], $donation->locale ?? 'ar')); ?>

        </td></tr>
    </table>
</body>
</html>
<?php /**PATH C:\Users\Hp\Desktop\etelaf-relief-laravel\resources\views/emails/certificate.blade.php ENDPATH**/ ?>