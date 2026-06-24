<?php
    $totalRaised = $latestDonations->sum('amount');
    $totalDonors = $latestDonations->unique('email')->count();
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings->show_donor_wall ?? true): ?>
<section class="donor-wall section" id="donorWall">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title"><?php echo e(__('donor_wall.title')); ?></h2>
            <p><?php echo e(__('donor_wall.subtitle')); ?></p>
        </div>

        <div class="donor-wall__layout">
            <div class="donor-wall__feed">
                <h3><i class="fas fa-clock"></i> <?php echo e(__('donor_wall.recent_donations')); ?></h3>
                <div class="donor-feed" id="donorFeed">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $latestDonations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $donation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="donor-entry">
                        <div class="donor-entry__avatar">
                            <?php echo e(strtoupper(substr($donation->is_anonymous ? __('common.anonymous') : $donation->donor_name, 0, 1))); ?>

                        </div>
                        <div class="donor-entry__info">
                            <strong class="donor-entry__name"><?php echo e($donation->is_anonymous ? __('common.anonymous') : $donation->donor_name); ?></strong>
                            <span class="donor-entry__meta">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($donation->project): ?>
                                    <?php echo e(trans_field($donation->project, 'title')); ?>

                                <?php elseif($donation->story): ?>
                                    <?php echo e(trans_field($donation->story, 'title')); ?>

                                <?php else: ?>
                                    <?php echo e(__('donate.general_donation')); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                · <?php echo e($donation->donated_at?->diffForHumans() ?: $donation->created_at->diffForHumans()); ?>

                            </span>
                        </div>
                        <span class="donor-entry__amount">$<?php echo e(number_format($donation->amount, 0)); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted"><?php echo e(__('donor_wall.no_donations')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="donor-wall__stats">
                <div class="donor-stat">
                    <span class="donor-stat__number" id="totalRaised" data-target="<?php echo e(number_format($totalRaised, 0, '', '')); ?>">$0</span>
                    <span class="donor-stat__label"><?php echo e(__('donor_wall.total_raised')); ?></span>
                </div>
                <div class="donor-stat">
                    <span class="donor-stat__number" id="totalDonors" data-target="<?php echo e($totalDonors); ?>">0</span>
                    <span class="donor-stat__label"><?php echo e(__('donor_wall.total_donors')); ?></span>
                </div>
                <div class="donor-stat">
                    <span class="donor-stat__number" id="totalDonations" data-target="<?php echo e($latestDonations->count()); ?>">0</span>
                    <span class="donor-stat__label"><?php echo e(__('donor_wall.total_donations')); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<section class="section" id="volunteer-contact" style="background:var(--bg-section, #f8fafc)">
    <div class="container">
        <div class="vc-row">
            <div class="vc-col">
                <div class="vc-card vc-card--volunteer">
                    <div class="vc-card__body" style="text-align:center">
                        <h2 style="margin:0 0 8px;font-size:1.2rem;font-weight:800;color:#fff"><?php echo e(__('volunteer.cta_title')); ?></h2>
                        <p style="margin:0 0 18px;font-size:0.88rem;color:rgba(255,255,255,.85);line-height:1.6"><?php echo e(__('volunteer.cta_desc')); ?></p>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center">
                            <a href="<?php echo e(route('volunteer.register', ['locale' => $currentLocale])); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;font-size:0.88rem;font-weight:600;color:#fff;background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.5);border-radius:10px;text-decoration:none;transition:all .2s" onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'"><i class="fas fa-hands-helping"></i> <?php echo e(__('volunteer.cta_btn')); ?></a>
                            <a href="<?php echo e(route('volunteer.dashboard', ['locale' => $currentLocale])); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;font-size:0.88rem;font-weight:600;color:#fff;background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.3);border-radius:10px;text-decoration:none;transition:all .2s" onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.12)'"><i class="fas fa-user-circle"></i> <?php echo e(__('volunteer.my_dashboard')); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vc-col">
                <div class="vc-card vc-card--contact">
                    <div class="vc-card__header">
                        <i class="fas fa-envelope" style="color:#059669"></i>
                        <h2 style="margin:0;font-size:1rem;font-weight:700;color:#1e293b"><?php echo e(__('common.contact_us')); ?></h2>
                    </div>
                    <div class="vc-card__body">
                        <form action="<?php echo e(route('contact.store', ['locale' => $currentLocale])); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
                                <input class="vci" type="text" name="name" required placeholder="<?php echo e(__('common.full_name')); ?>">
                                <input class="vci" type="email" name="email" required placeholder="<?php echo e(__('common.email')); ?>">
                            </div>
                            <input class="vci" type="text" name="subject" required placeholder="<?php echo e(__('common.subject')); ?>" style="margin-bottom:8px">
                            <textarea class="vci" name="message" rows="3" required placeholder="<?php echo e(__('common.message')); ?>" style="margin-bottom:10px;resize:vertical;min-height:60px;font-family:inherit"></textarea>
                            <button type="submit" style="display:flex;align-items:center;gap:6px;padding:9px 22px;font-size:0.85rem;font-weight:600;color:#fff;background:linear-gradient(135deg,#059669,#10b981);border:none;border-radius:9px;cursor:pointer;width:100%;justify-content:center;font-family:inherit;transition:all .2s" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'"><i class="fas fa-paper-plane"></i> <?php echo e(__('common.send_message')); ?></button>
                        </form>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->whatsapp): ?>
                            <a href="https://wa.me/<?php echo e(preg_replace('/\D/', '', $s->whatsapp)); ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:5px;font-size:0.78rem;color:#475569;text-decoration:none;padding:6px 12px;background:#f1f5f9;border-radius:8px;transition:all .2s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'"><i class="fab fa-whatsapp" style="color:#25d366"></i> <?php echo e($s->whatsapp); ?></a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->email): ?>
                            <a href="mailto:<?php echo e($s->email); ?>" style="display:flex;align-items:center;gap:5px;font-size:0.78rem;color:#475569;text-decoration:none;padding:6px 12px;background:#f1f5f9;border-radius:8px;transition:all .2s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'"><i class="fas fa-envelope" style="color:#059669"></i> <?php echo e($s->email); ?></a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
.vc-row{display:flex;gap:1.25rem;align-items:stretch}
.vc-col{flex:1;min-width:0;display:flex}
.vc-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;width:100%;display:flex;flex-direction:column}
.vc-card--volunteer{background:linear-gradient(135deg,#065f46,#059669);border:none;justify-content:center}
.vc-card--contact .vc-card__header{display:flex;align-items:center;gap:8px;padding:14px 18px;border-bottom:1px solid #f1f5f9;background:#fafafa}
.vc-card--contact .vc-card__body{padding:14px 18px 18px;flex:1}
.vci{width:100%;padding:8px 11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.82rem;background:#fafafa;transition:all .2s;box-sizing:border-box;color:#1e293b;font-family:inherit}
.vci:focus{outline:none;border-color:#10b981;background:#fff;box-shadow:0 0 0 3px rgba(16,185,129,.1)}
@media(max-width:768px){.vc-row{flex-direction:column}}
</style>
<?php /**PATH D:\etelaf-relief-laravel\resources\views/partials/home-bottom.blade.php ENDPATH**/ ?>