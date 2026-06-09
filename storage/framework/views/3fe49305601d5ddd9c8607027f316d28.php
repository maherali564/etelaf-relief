<!DOCTYPE html>
<html dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>" lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #f0fdf4;
            width: 100%; height: 100%;
        }
        .certificate-wrap {
            width: 100%; height: 100%;
            padding: 30px;
            position: relative;
        }
        .certificate-border {
            border: 12px double #059669;
            padding: 30px 40px;
            background: #fff;
            min-height: 700px;
            position: relative;
            border-radius: 8px;
        }
        .certificate-border::before {
            content: '';
            position: absolute;
            top: 10px; left: 10px; right: 10px; bottom: 10px;
            border: 2px solid #a7f3d0;
            border-radius: 4px;
            pointer-events: none;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #059669;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 28px;
            color: #059669;
            letter-spacing: 2px;
        }
        .header p {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
        .seal {
            text-align: center;
            margin: 20px 0;
        }
        .seal-circle {
            display: inline-block;
            width: 80px; height: 80px;
            border-radius: 50%;
            border: 4px solid #059669;
            line-height: 72px;
            font-size: 32px;
            font-weight: bold;
            color: #059669;
        }
        .body-text {
            text-align: center;
            margin: 15px 0;
        }
        .body-text .certifies {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 5px;
        }
        .body-text .donor-name {
            font-size: 32px;
            font-weight: bold;
            color: #1e293b;
            margin: 10px 0;
        }
        .body-text .amount {
            font-size: 26px;
            font-weight: bold;
            color: #059669;
            margin: 10px 0;
        }
        .body-text .detail {
            font-size: 13px;
            color: #64748b;
            margin: 4px 0;
        }
        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px; right: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 2px solid #e2e8f0;
            padding-top: 15px;
        }
        .footer-left {
            font-size: 11px;
            color: #94a3b8;
        }
        .footer-right {
            text-align: center;
        }
        .footer-right .signature-line {
            width: 180px;
            border-top: 1px solid #1e293b;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 11px;
            color: #64748b;
        }
        .certificate-id {
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
            margin-top: 10px;
        }
        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 120px;
            color: rgba(5, 150, 105, 0.04);
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="certificate-wrap">
        <div class="certificate-border">
            <div class="watermark"><?php echo e(config('app.name')); ?></div>

            <div class="header">
                <h1><?php echo e(__('certificate.title')); ?></h1>
                <p><?php echo e(config('app.name')); ?> — <?php echo e(__('certificate.organization')); ?></p>
            </div>

            <div class="seal">
                <div class="seal-circle">
                    <span><?php echo e(strtoupper(substr(__('certificate.seal'), 0, 1))); ?></span>
                </div>
            </div>

            <div class="body-text">
                <p class="certifies"><?php echo e(__('certificate.certifies')); ?></p>
                <p class="donor-name"><?php echo e($donation->is_anonymous ? __('common.anonymous') : $donation->donor_name); ?></p>
                <p class="certifies"><?php echo e(__('certificate.has_donated')); ?></p>
                <p class="amount">$<?php echo e(number_format($donation->amount, 2)); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($donation->campaign): ?>
                    <p class="detail"><?php echo e(__('certificate.to_campaign')); ?> <?php echo e(trans_field($donation->campaign, 'title')); ?></p>
                <?php elseif($donation->project): ?>
                    <p class="detail"><?php echo e(__('certificate.to_project')); ?> <?php echo e(trans_field($donation->project, 'title')); ?></p>
                <?php elseif($donation->story): ?>
                    <p class="detail"><?php echo e(__('certificate.to_story')); ?> <?php echo e(trans_field($donation->story, 'title')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="detail"><?php echo e(__('certificate.on_date')); ?> <?php echo e($donation->donated_at?->format('Y/m/d') ?: $donation->created_at->format('Y/m/d')); ?></p>
                <p class="detail"><?php echo e(__('certificate.transaction')); ?>: #<?php echo e($donation->transaction_id ?? $donation->id); ?></p>
            </div>

            <div class="footer">
                <div class="footer-left">
                    <?php echo e(__('certificate.verify_at')); ?> <?php echo e(url('/verify/donation/' . $donation->id)); ?>

                </div>
                <div class="footer-right">
                    <div class="signature-line"><?php echo e(__('certificate.authorized_signature')); ?></div>
                </div>
            </div>

            <div class="certificate-id">
                <?php echo e(__('certificate.certificate_no')); ?>: <?php echo e(config('app.name')); ?>-DON-<?php echo e(str_pad($donation->id, 6, '0', STR_PAD_LEFT)); ?>-<?php echo e($donation->created_at->format('Y')); ?>

            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Hp\Desktop\etelaf-relief-laravel\resources\views/pdf/certificate.blade.php ENDPATH**/ ?>