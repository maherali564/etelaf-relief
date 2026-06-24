<?php $__env->startSection('title', trans_field($project, 'title')); ?>
<?php $__env->startSection('og_image', $project->first_image ? asset('storage/'.$project->first_image) : ''); ?>
<?php $__env->startSection('content'); ?>

<?php
$allImages = $project->images ?? [];
if ($project->image && !in_array($project->image, $allImages)) {
    array_unshift($allImages, $project->image);
}
$allVideos = [];
if ($project->video_url) {
    $allVideos[] = ['url' => $project->video_url, 'type' => $project->video_type, 'thumbnail' => $project->video_thumbnail];
} elseif (!empty($project->videos)) {
    $projectVids = is_array($project->videos) ? $project->videos : json_decode($project->videos, true) ?? [];
    foreach ($projectVids as $v) {
        $thumb = 'thumbnails/' . pathinfo($v, PATHINFO_FILENAME) . '.jpg';
        $allVideos[] = ['url' => route('storage.video', ['path' => $v]), 'type' => 'local', 'thumbnail' => $thumb];
    }
}
$allMedia = [];
foreach ($allImages as $ii => $img) {
    $allMedia[] = ['type' => 'image', 'src' => asset('storage/'.$img), 'thumb' => asset('storage/'.$img), 'imgIndex' => $ii];
}
foreach ($allVideos as $vi => $v) {
    $thumbUrl = $v['thumbnail']
        ? ($v['type'] === 'youtube' ? 'https://img.youtube.com/vi/'.preg_replace('/[^a-zA-Z0-9_-]/', '', $v['url']).'/default.jpg' : asset('storage/'.$v['thumbnail']))
        : '';
    $allMedia[] = ['type' => 'video', 'src' => $v['url'], 'thumb' => $thumbUrl, 'vidIndex' => $vi];
}
$totalRaised = (int) ($project->raised_amount ?? 0);
$totalGoal = (int) ($project->goal_amount ?? 0);
$progressPct = $totalGoal > 0 ? min(100, round($totalRaised / $totalGoal * 100)) : 0;
$remaining = max(0, $totalGoal - $totalRaised);
$latestDonation = $donations->first();
$donorCount = $donations->pluck('donor_name')->unique()->filter()->count();
$projectLocation = trans_field($project, 'location') ?? __('common.not_specified');
$publishedDate = $project->created_at ? $project->created_at->format('Y-m-d') : '—';
?>

<?php $__env->startPush('head'); ?>
<style>
/* Hero top gradient → blends white navbar into hero image */
.hero-detailed::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 100px;
    background: linear-gradient(to bottom, #fff, transparent);
    pointer-events: none; z-index: 2;
}
</style>
<?php $__env->stopPush(); ?>

<section class="hero-detailed" <?php if($project->first_image): ?> style="background-image:url('<?php echo e(asset('storage/'.$project->first_image)); ?>')" <?php endif; ?>>
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-project-diagram"></i> <?php echo e(__('home.projects_tag') ?? __('site.nav_projects')); ?></span>
        <h1 class="hero-detailed__title"><?php echo e(trans_field($project, 'title')); ?></h1>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->goal_amount > 0): ?>
        <div class="hero-detailed__stats">
            <div class="hero-detailed__stat"><span class="hero-detailed__stat-value">$<?php echo e(number_format($project->raised_amount ?? 0)); ?></span><span class="hero-detailed__stat-label"><?php echo e(__('site.raised')); ?></span></div>
            <div class="hero-detailed__stat"><span class="hero-detailed__stat-value">$<?php echo e(number_format($project->goal_amount)); ?></span><span class="hero-detailed__stat-label"><?php echo e(__('site.goal')); ?></span></div>
            <div class="hero-detailed__stat"><span class="hero-detailed__stat-value"><?php echo e($project->progressPercent()); ?>%</span><span class="hero-detailed__stat-label"><?php echo e(__('common.progress')); ?></span></div>
        </div>
        <?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['raised' => $project->raised_amount ?? 0,'goal' => $project->goal_amount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['raised' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->raised_amount ?? 0),'goal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->goal_amount)]); ?>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="donation-layout">

            
            <div class="gallery-container">

                <div class="main-image" id="mainImageWrap">
                    <img id="mainImage" class="main-img-visible" src="<?php echo e(asset('storage/'.$allImages[0])); ?>" alt="" onclick="openLightbox(0)">
                    <img id="mainImageNext" class="main-img-hidden" src="" alt="" aria-hidden="true">
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($allMedia) > 0): ?>
                <div class="gallery-tabs">
                    <button class="gallery-tab gallery-tab--active" data-filter="all" onclick="filterMedia('all',this)"><?php echo e(__('common.all') ?? 'All'); ?></button>
                    <button class="gallery-tab" data-filter="image" onclick="filterMedia('image',this)"><?php echo e(__('common.images') ?? 'Images'); ?></button>
                    <button class="gallery-tab" data-filter="video" onclick="filterMedia('video',this)"><?php echo e(__('common.videos') ?? 'Videos'); ?></button>
                </div>

                <div class="media-thumbs" id="mediaThumbs">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allMedia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button class="thumb-item <?php echo e($loop->first ? 'thumb-item--active' : ''); ?> <?php echo e($m['type'] === 'video' ? 'thumb-item--video' : ''); ?>"
                            data-media-type="<?php echo e($m['type']); ?>"
                            onclick="<?php echo e($m['type'] === 'image' ? "setMainImage({$m['imgIndex']});openLightbox({$m['imgIndex']})" : "openVideoModal({$m['vidIndex']})"); ?>"
                            aria-label="<?php echo e($m['type'] === 'image' ? 'Image '.($m['imgIndex']+1) : 'Video '.($m['vidIndex']+1)); ?>">
                        <img src="<?php echo e($m['thumb']); ?>" alt="" loading="lazy">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($m['type'] === 'video'): ?>
                        <span class="thumb-item__play"><i class="fas fa-play"></i></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="project-detail-card">
                    <h2 class="project-detail-card__title"><?php echo e(trans_field($project, 'title')); ?></h2>

                    <div class="project-detail-card__meta">
                        <div class="project-detail-card__meta-row">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?php echo e(__('common.published_date') ?? 'تاريخ النشر'); ?>: <?php echo e($publishedDate); ?></span>
                        </div>
                        <div class="project-detail-card__meta-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo e(__('common.location') ?? 'المكان'); ?>: <?php echo e($projectLocation); ?></span>
                        </div>
                    </div>

                    <div class="project-detail-card__desc">
                        <?php echo e(trans_field($project, 'description')); ?>

                    </div>

                    <?php $shareUrl = urlencode(url()->current()); $shareText = urlencode(trans_field($project, 'title')); ?>
                    <div class="project-detail-card__share">
                        <a href="https://wa.me/?text=<?php echo e($shareText.'%0A'.$shareUrl); ?>" target="_blank" rel="noopener" class="share-circle share-circle--whatsapp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo e($shareText); ?>&url=<?php echo e($shareUrl); ?>" target="_blank" rel="noopener" class="share-circle share-circle--twitter" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.facebook.com/sharer.php?u=<?php echo e($shareUrl); ?>" target="_blank" rel="noopener" class="share-circle share-circle--facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>

            </div>

            
            <div class="form-container">
                <div class="pcard">
                    <div class="pcard__head">
                        <div class="pcard__head-icon"><i class="fas fa-hand-holding-heart"></i></div>
                        <div>
                            <h2 class="pcard__head-title"><?php echo e(__('common.contribute')); ?></h2>
                            <p class="pcard__head-sub"><?php echo e(__('home.donate_desc')); ?></p>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalGoal > 0): ?>
                    <div class="pcard__progress">
                        <div class="pcard__stats">
                            <div class="pstat pstat--main"><i class="fas fa-hand-holding-heart"></i> <span>$<?php echo e(number_format($totalRaised)); ?></span> <small><?php echo e(__('common.raised')); ?></small></div>
                            <div class="pstat pstat--dim"><i class="fas fa-users"></i> <span><?php echo e(number_format(max($donorCount, $donations->count()))); ?></span> <small><?php echo e(__('common.donors')); ?></small></div>
                        </div>
                        <div class="pcard__bar-wrap"><div class="pcard__bar" style="width:<?php echo e($progressPct); ?>%"></div></div>
                        <div class="pcard__pct"><?php echo e($progressPct); ?>% <?php echo e(__('common.completed')); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remaining > 0): ?>
                        <div class="pcard__remain"><i class="fas fa-hourglass-half"></i> <?php echo e(__('common.remaining_goal', ['amount' => number_format($remaining)])); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($latestDonation): ?>
                    <div class="pcard__latest">
                        <i class="fas fa-bolt" style="color:#f59e0b"></i>
                        <strong><?php echo e($latestDonation->donor_name ?: __('common.anonymous')); ?></strong> <?php echo e(__('common.donated')); ?>

                        <strong>$<?php echo e(number_format($latestDonation->amount, 0)); ?></strong>
                        <span><?php echo e($latestDonation->created_at->diffForHumans()); ?></span>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <form method="POST" action="<?php echo e(route('donate.store', $currentLocale)); ?>" class="pcard__form">
                        <input type="hidden" name="project_id" value="<?php echo e($project->id); ?>">
                        <input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                        <?php echo $__env->yieldContent('donate_entity_fields'); ?>

                        <div class="pfld">
                            <label class="pfld__label"><?php echo e(__('donate.quick_amounts')); ?></label>
                            <div class="ppreset">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [10,25,50,100,250,500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="ppreset__btn" data-a="<?php echo e($p); ?>">$<?php echo e($p); ?></button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="pfld">
                            <div class="pgrid pgrid--2">
                                <div><label class="pfld__label"><?php echo e(__('donate.custom_amount')); ?> <span style="color:#ef4444">*</span></label><input class="pi" type="number" name="amount" id="donationAmount" min="1" step="0.01" required placeholder="<?php echo e(__('donate.min_amount')); ?>"></div>
                                <div><label class="pfld__label"><?php echo e(__('common.full_name')); ?> <span style="color:#ef4444">*</span></label><input class="pi" type="text" name="donor_name" required placeholder="<?php echo e(__('common.full_name')); ?>"></div>
                                <div><label class="pfld__label"><?php echo e(__('common.email')); ?> <span style="color:#ef4444">*</span></label><input class="pi" type="email" name="email" required placeholder="example@domain.com"></div>
                                <div><label class="pfld__label"><?php echo e(__('common.phone')); ?></label><input class="pi" type="tel" name="phone" placeholder="05xxxxxxxx"></div>
                            </div>
                        </div>

                        <div class="pfld">
                            <label class="pfld__label"><?php echo e(__('donate.payment_method')); ?></label>
                            <select class="pi" name="payment_method_id" id="paymentMethodSelect" required>
                                <option value=""><?php echo e(__('donate.select_payment_method')); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($pm->id); ?>" data-driver="<?php echo e($pm->gateway?->driver ?? ''); ?>"><?php echo e($pm->name); ?> - <?php echo e($pm->description); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>

                        <?php $cryptoJson = $cryptocurrencies->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'symbol' => $c->symbol, 'networks' => $c->networks->map(fn($n) => ['id' => $n->id, 'name' => $n->network_name])]); ?>
                        <script>window.cryptoCurrencies = <?php echo json_encode($cryptoJson); ?>;</script>
                        <div id="cryptoSection" class="pfld" style="display:none">
                            <div class="pgrid pgrid--2">
                                <div><label class="pfld__label"><?php echo e(__('donate.select_crypto')); ?></label><select class="pi" name="cryptocurrency_id" id="cryptoCurrencySelect"><option value=""><?php echo e(__('donate.choose_crypto')); ?></option></select></div>
                                <div id="cryptoNetworkGroup" style="display:none"><label class="pfld__label"><?php echo e(__('donate.select_network')); ?></label><select class="pi" name="crypto_network_id" id="cryptoNetworkSelect"><option value=""><?php echo e(__('donate.choose_network')); ?></option></select></div>
                            </div>
                        </div>

                        <div class="pfld">
                            <label class="pcheck"><input type="checkbox" name="is_anonymous" value="1"> <span><?php echo e(__('donate.anonymous_donation')); ?> <small><?php echo e(__('donate.anonymous_hint')); ?></small></span></label>
                            <label class="pcheck"><input type="checkbox" name="is_recurring" value="1" id="recurringToggle"> <span><?php echo e(__('donate.recurring_donation')); ?></span></label>
                        </div>

                        <div id="recurringOptions" class="pfld" style="display:none">
                            <label class="pfld__label"><?php echo e(__('donate.recurring_interval')); ?></label>
                            <select class="pi" name="recurring_interval">
                                <option value="monthly"><?php echo e(__('donate.every_month')); ?></option>
                                <option value="quarterly"><?php echo e(__('donate.every_3_months')); ?></option>
                                <option value="yearly"><?php echo e(__('donate.every_year')); ?></option>
                            </select>
                        </div>

                        <div class="pfld" style="margin-bottom:0">
                            <label class="pfld__label"><?php echo e(__('donate.donation_note')); ?></label>
                            <textarea class="pi pi--area" name="notes" rows="2" placeholder="<?php echo e(__('donate.donation_note_placeholder')); ?>"></textarea>
                        </div>

                        <button type="submit" class="pbtn"><i class="fas fa-heart"></i> <?php echo e(__('common.complete_donation')); ?></button>
                        <p class="psecure"><i class="fas fa-lock"></i> <?php echo e(__('donate.secure_notice')); ?></p>
                    </form>

                </div>
            </div>

        </div>
    </div>
</section>

<?php $projectDonations = $donations ?? $project->donations()->completed()->latest()->limit(20)->get(); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projectDonations->isNotEmpty()): ?>
<section class="section section--muted">
    <div class="container">
        <h2 class="section-title section-title--center"><?php echo e(__('donor_wall.recent_donations')); ?></h2>
        <div class="donor-list">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $projectDonations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="donor-list__row">
                <span class="donor-list__avatar"><?php echo e(strtoupper(substr($d->is_anonymous ? __('common.anonymous') : $d->donor_name, 0, 1))); ?></span>
                <div class="donor-list__info">
                    <strong><?php echo e($d->is_anonymous ? __('common.anonymous') : $d->donor_name); ?></strong>
                    <span class="donor-list__meta"><?php echo e($d->donated_at?->diffForHumans() ?: $d->created_at->diffForHumans()); ?></span>
                </div>
                <span class="donor-list__amount">$<?php echo e(number_format($d->amount, 0)); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($similar) && $similar->isNotEmpty()): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title section-title--center"><?php echo e(__('common.similar_projects') ?? 'مشاريع مشابهة'); ?></h2>
        <div class="similar-grid">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $similar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('projects.show', ['locale' => $currentLocale, 'slug' => $s->slug])); ?>" class="similar-card">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->first_image): ?>
                <div class="similar-card__image" style="background-image:url('<?php echo e(asset('storage/'.$s->first_image)); ?>')"></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="similar-card__body">
                    <h4><?php echo e(trans_field($s, 'title')); ?></h4>
                    <?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['raised' => $s->raised_amount ?? 0,'goal' => $s->goal_amount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['raised' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s->raised_amount ?? 0),'goal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s->goal_amount)]); ?>
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
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div id="lightbox" class="lightbox-overlay" onclick="closeLightbox(event)" style="display:none" role="dialog" aria-label="Image viewer">
    <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
    <button class="lightbox-nav lightbox-nav--prev" onclick="navigateLightbox(-1)">&#8249;</button>
    <img id="lightboxImg" class="lightbox-image" alt="">
    <button class="lightbox-nav lightbox-nav--next" onclick="navigateLightbox(1)">&#8250;</button>
    <div class="lightbox-counter" id="lightboxCounter"></div>
</div>

<div id="videoModal" class="lightbox-overlay lightbox-overlay--video" onclick="closeVideoModal(event)" style="display:none" role="dialog" aria-label="Video player">
    <button class="lightbox-close" onclick="closeVideoModal()">&times;</button>
    <div class="video-wrapper">
        <video id="videoEl" class="video-player" controls playsinline preload="auto"></video>
        <iframe id="videoIframe" class="video-player" style="display:none" allow="autoplay; fullscreen" allowfullscreen></iframe>
    </div>
</div>

<style>
/* ════════════ Layout ════════════ */
.donation-layout { display: flex; flex-direction: row; gap: 40px; align-items: flex-start; }
.gallery-container { flex: 0 0 60%; min-width: 0; }
.form-container { flex: 0 0 calc(40% - 40px); min-width: 0; position: sticky; top: 100px; }
@media (max-width: 900px) {
    .donation-layout { flex-direction: column; }
    .gallery-container, .form-container { flex: 1; position: static; }
}

/* ════════════ Hero Section with Gradient ════════════ */
.hero-detailed {
    position: relative;
    min-height: 320px;
    display: flex; align-items: center; justify-content: center;
    background-size: cover; background-position: center;
}
.hero-detailed::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0;
    height: 140px;
    background: linear-gradient(to bottom, transparent, #f8f9fa);
    pointer-events: none; z-index: 2;
}
.hero-detailed__overlay { background: linear-gradient(180deg, rgba(0,0,0,.65) 0%, rgba(0,0,0,.35) 100%); }
.hero-detailed__inner { z-index: 3; }
.hero-detailed__title { font-size: 2.5rem; font-weight: 700; color: #fff; line-height: 1.2; }
.hero-detailed__tag { font-size: .85rem; }

/* ════════════ Main Image ════════════ */
.main-image {
    width: 100%; min-height: 320px; max-height: 65vh;
    border-radius: 14px; overflow: hidden; cursor: pointer;
    background: #f1f5f9; border: 1px solid #e2e8f0;
    margin-bottom: 12px; position: relative;
}
.main-image img {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: contain; display: block; transition: opacity .3s ease;
}
.main-img-visible { z-index: 1; opacity: 1; }
.main-img-hidden { z-index: 2; opacity: 0; }

/* ════════════ Gallery Tabs ════════════ */
.gallery-tabs { display: flex; gap: 6px; margin-bottom: 12px; }
.gallery-tab {
    padding: 6px 16px; border-radius: 20px; border: 2px solid #e2e8f0;
    background: #fff; color: #64748b; font-size: .82rem; font-weight: 600; cursor: pointer;
    transition: all .2s ease;
}
.gallery-tab:hover { border-color: #10b981; color: #10b981; }
.gallery-tab--active { border-color: #10b981; background: #10b981; color: #fff; }

/* ════════════ Media Thumbs ════════════ */
.media-thumbs { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
.thumb-item {
    flex: 0 0 calc((100% - 40px) / 5); aspect-ratio: 4/3;
    border-radius: 10px; overflow: hidden;
    border: 2px solid transparent; padding: 0; cursor: pointer;
    background: #f1f5f9; transition: all .25s ease; position: relative;
}
.thumb-item:hover { transform: scale(1.04); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
.thumb-item--active { border-color: #10b981; }
.thumb-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb-item--hidden { display: none; }
.thumb-item__play {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    pointer-events: none; color: #fff; font-size: 1.4rem;
    background: rgba(0,0,0,.2); transition: background .25s ease;
}
.thumb-item--video:hover .thumb-item__play { background: rgba(0,0,0,.4); }

/* ════════════ Project Details Card ════════════ */
.project-detail-card {
    background: #fff; border-radius: 12px; padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,.08); border: 1px solid #f1f5f9;
    margin-top: 16px;
}
.project-detail-card__title {
    font-size: 1.35rem; font-weight: 800; color: #059669;
    margin: 0 0 8px; line-height: 1.3;
}
.project-detail-card__meta { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
.project-detail-card__meta-row {
    display: flex; align-items: center; gap: 6px;
    font-size: .8rem; color: #64748b;
}
.project-detail-card__meta-row i { color: #10b981; width: 14px; text-align: center; font-size: .85rem; flex-shrink: 0; }
.project-detail-card__desc {
    font-size: .92rem; line-height: 1.7; color: #374151;
    overflow-wrap: break-word; word-wrap: break-word;
    max-height: 160px; overflow-y: auto;
    padding: 12px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;
    margin-bottom: 12px;
}
.project-detail-card__desc::-webkit-scrollbar { width: 4px; }
.project-detail-card__desc::-webkit-scrollbar-track { background: transparent; }
.project-detail-card__desc::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }
.project-detail-card__share { display: flex; gap: 8px; }
.share-circle {
    width: 36px; height: 36px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: .95rem; text-decoration: none;
    transition: transform .2s ease;
}
.share-circle:hover { transform: scale(1.15); }
.share-circle--whatsapp { background: #25d366; }
.share-circle--twitter { background: #1da1f2; }
.share-circle--facebook { background: #1877f2; }

/* ════════════ Donation Form Card ════════════ */
.pcard { background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.08); border:1px solid #e2e8f0; overflow:hidden }
.pcard__head { display:flex; align-items:center; gap:10px; padding:12px 16px; background:linear-gradient(135deg,#059669,#10b981) }
.pcard__head-icon { width:34px; height:34px; background:rgba(255,255,255,.15); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.95rem; color:#fff; flex-shrink:0 }
.pcard__head-title { margin:0; font-size:.9rem; font-weight:800; color:#fff }
.pcard__head-sub { margin:1px 0 0; font-size:.7rem; color:rgba(255,255,255,.75) }

/* Progress */
.pcard__progress { padding:12px 16px 10px; border-bottom:1px solid #f1f5f9 }
.pcard__stats { display:flex; gap:12px; margin-bottom:8px; justify-content:center }
.pstat { text-align:center }
.pstat i { display:block; font-size:.8rem; color:#10b981; margin-bottom:1px }
.pstat small { display:block; color:#94a3b8; font-size:.6rem; text-transform:uppercase; letter-spacing:.3px }
.pstat--main { flex:0 0 60% }
.pstat--main span { display:block; font-size:1.15rem; font-weight:800; color:#065f46 }
.pstat--dim { flex:0 0 40% }
.pstat--dim span { display:block; font-size:.72rem; font-weight:400; color:#94a3b8; margin-top:5px }
.pcard__bar-wrap { height:5px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-bottom:3px }
.pcard__bar { height:100%; background:linear-gradient(90deg,#059669,#10b981); border-radius:999px; transition:width .5s ease }
.pcard__pct { text-align:center; font-size:.65rem; color:#94a3b8; margin-bottom:4px }
.pcard__remain { font-size:.72rem; color:#065f46; font-weight:600; text-align:center; display:flex; align-items:center; justify-content:center; gap:4px }

/* Latest Donation */
.pcard__latest { padding:6px 16px; font-size:.72rem; color:#64748b; background:#fffbeb; border-bottom:1px solid #fef3c7; display:flex; align-items:center; gap:4px; flex-wrap:wrap }
.pcard__latest strong { color:#1e293b }
.pcard__latest span { color:#94a3b8; font-size:.68rem }

/* Form */
.pcard__form { padding:10px 16px 14px }
.pfld { margin-bottom:9px }
.pfld__label { display:block; margin-bottom:3px; font-size:.65rem; font-weight:700; color:#374151; text-transform:uppercase }
.pgrid { display:grid; gap:8px }
.pgrid--2 { grid-template-columns:1fr 1fr }
.pi { width:100%; padding:7px 10px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:.8rem; background:#fafafa; transition:all .2s; box-sizing:border-box; color:#1e293b; font-family:inherit }
.pi:hover { border-color:#cbd5e1; background:#fff }
.pi:focus { outline:none; border-color:#10b981; background:#fff; box-shadow:0 0 0 3px rgba(16,185,129,.1) }
.pi--area { resize:vertical; min-height:48px; font-family:inherit }

/* Presets */
.ppreset { display:flex; flex-wrap:wrap; gap:4px }
.ppreset__btn { padding:5px 13px; border:1.5px solid #e2e8f0; border-radius:6px; background:#fff; font-size:.75rem; font-weight:600; color:#475569; cursor:pointer; transition:all .15s; font-family:inherit }
.ppreset__btn:hover { border-color:#10b981; color:#059669; background:#f0fdf4 }
.ppreset__btn--active { border-color:#059669; background:#059669; color:#fff }

/* Checkboxes */
.pcheck { display:flex; align-items:center; gap:6px; cursor:pointer; font-size:.75rem; color:#475569; padding:2px 0 }
.pcheck input[type=checkbox] { width:14px; height:14px; accent-color:#10b981; cursor:pointer; flex-shrink:0 }
.pcheck small { color:#94a3b8; font-size:.7rem }

/* Submit */
.pbtn { display:flex; align-items:center; gap:6px; padding:10px 20px; font-size:.85rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#059669,#10b981); border:none; border-radius:9px; cursor:pointer; width:100%; justify-content:center; font-family:inherit; transition:all .2s; box-shadow:0 3px 10px rgba(16,185,129,.25); margin-top:4px }
.pbtn:hover { transform:translateY(-1px); box-shadow:0 5px 16px rgba(16,185,129,.35) }
.pbtn:active { transform:translateY(0) }
.psecure { margin:8px 0 0; font-size:.68rem; color:#94a3b8; display:flex; align-items:center; gap:4px; justify-content:center }

/* ════════════ Lightbox ════════════ */
.lightbox-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.92); z-index: 9999;
    display: flex; align-items: center; justify-content: center; animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.lightbox-overlay--video { z-index: 10000; }
.lightbox-close {
    position: absolute; top: 16px; <?php echo e($isRtl ? 'left' : 'right'); ?>: 16px;
    background: rgba(255,255,255,.1); border: none; color: #fff; font-size: 28px;
    width: 44px; height: 44px; border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center; z-index: 10;
}
.lightbox-close:hover { background: rgba(255,255,255,.2); }
.lightbox-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,.12); border: none; color: #fff; font-size: 40px;
    width: 48px; height: 48px; border-radius: 50%; cursor: pointer; opacity: .7;
    display: flex; align-items: center; justify-content: center;
}
.lightbox-nav:hover { opacity: 1; background: rgba(255,255,255,.25); }
.lightbox-nav--prev { <?php echo e($isRtl ? 'right' : 'left'); ?>: 16px; }
.lightbox-nav--next { <?php echo e($isRtl ? 'left' : 'right'); ?>: 16px; }
.lightbox-image { max-width: 90%; max-height: 85%; object-fit: contain; border-radius: 6px; box-shadow: 0 8px 40px rgba(0,0,0,.5); }
.lightbox-counter { position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); color: rgba(255,255,255,.5); font-size: 13px; }

/* ════════════ Video Modal ════════════ */
.video-wrapper { width: 90vw; max-width: 960px; height: 60vh; max-height: 80vh; display: flex; align-items: center; justify-content: center; }
.video-player { width: 100%; height: 100%; border-radius: 8px; box-shadow: 0 8px 40px rgba(0,0,0,.5); background: #000; }

/* ════════════ Donor List ════════════ */
.section--muted { background: #f8fafc; }
.section-title--center { text-align: center; margin-bottom: 1.5rem; }
.donor-list { max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; }
.donor-list__row { display: flex; align-items: center; gap: .75rem; padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9; }
.donor-list__row:last-child { border-bottom: none; }
.donor-list__row:hover { background: #f8fafc; }
.donor-list__avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg,#059669,#10b981); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .85rem; flex-shrink: 0; }
.donor-list__info { flex: 1; min-width: 0; }
.donor-list__info strong { display: block; font-size: .9rem; color: #1e293b; }
.donor-list__meta { font-size: .8rem; color: #64748b; }
.donor-list__amount { font-size: 1rem; color: #10b981; font-weight: 700; white-space: nowrap; }

/* ════════════ Responsive ════════════ */
@media (max-width: 900px) {
    .project-detail-card__desc { max-height: none; }
}
@media (max-width: 768px) {
    .thumb-item { flex: 0 0 calc((100% - 30px) / 4); }
    .hero-detailed__title { font-size: 1.6rem; }
}
@media (max-width: 480px) {
    .thumb-item { flex: 0 0 calc((100% - 10px) / 2); }
    .lightbox-nav { width: 36px; height: 36px; font-size: 28px; }
    .video-wrapper { width: 96vw; height: 50vh; }
}
</style>

<script>
var lightboxImages = <?php echo json_encode(array_map(fn($img) => asset('storage/'.$img), $allImages)); ?>;
var vidUrls = <?php echo json_encode(array_map(fn($v) => $v['url'], $allVideos)); ?>;
var vidTypes = <?php echo json_encode(array_map(fn($v) => $v['type'], $allVideos)); ?>;
var vidEmbeds = <?php echo json_encode(array_map(fn($v) => ($v['type']==='youtube' ? 'https://www.youtube.com/embed/'.preg_replace('/[^a-zA-Z0-9_-]/','',$v['url']) : ($v['type']==='vimeo' ? 'https://player.vimeo.com/video/'.intval(preg_replace('/[^0-9]/','',$v['url'])) : '')), $allVideos)); ?>;
var li = 0;
function openLightbox(i){li=i;document.getElementById('lightbox').style.display='flex';document.getElementById('lightboxImg').src=lightboxImages[li];updateCounter();document.body.style.overflow='hidden';}
function closeLightbox(e){if(e&&e.target!==e.currentTarget)return;document.getElementById('lightbox').style.display='none';document.body.style.overflow='';}
function navigateLightbox(d){li=(li+d+lightboxImages.length)%lightboxImages.length;document.getElementById('lightboxImg').src=lightboxImages[li];updateCounter();}
function updateCounter(){document.getElementById('lightboxCounter').textContent=(li+1)+' / '+lightboxImages.length;}

function setMainImage(i){
    var cur=document.getElementById('mainImage'),nxt=document.getElementById('mainImageNext');
    nxt.src=lightboxImages[i];nxt.style.opacity='1';
    setTimeout(function(){cur.src=nxt.src;nxt.style.opacity='0';},300);
    document.querySelectorAll('.media-thumbs .thumb-item').forEach(function(e,j){
        var idx=j;['thumb-item--active'].forEach(function(c){e.classList.toggle(c,idx===i);});
    });
}

function filterMedia(t,btn){
    document.querySelectorAll('.gallery-tab').forEach(function(tab){tab.classList.remove('gallery-tab--active');});
    btn.classList.add('gallery-tab--active');
    document.querySelectorAll('.media-thumbs .thumb-item').forEach(function(th){
        var match=t==='all'||th.getAttribute('data-media-type')===t;
        th.classList.toggle('thumb-item--hidden',!match);
    });
}

var vi=0;
function openVideoModal(i){vi=i;document.getElementById('videoModal').style.display='flex';document.body.style.overflow='hidden';var el=document.getElementById('videoEl');var ifr=document.getElementById('videoIframe');el.style.display='none';ifr.style.display='none';el.pause();el.src='';ifr.src='';if(vidTypes[vi]==='youtube'||vidTypes[vi]==='vimeo'){ifr.style.display='block';ifr.src=vidEmbeds[vi];}else{el.style.display='block';el.src=vidUrls[vi];el.play();}}
function closeVideoModal(e){if(e&&e.target!==e.currentTarget)return;var el=document.getElementById('videoEl');var ifr=document.getElementById('videoIframe');el.pause();el.src='';ifr.src='';document.getElementById('videoModal').style.display='none';document.body.style.overflow='';}
document.addEventListener('keydown',function(e){if(document.getElementById('lightbox').style.display==='flex'){if(e.key==='Escape')closeLightbox();if(e.key==='ArrowLeft')navigateLightbox(-1);if(e.key==='ArrowRight')navigateLightbox(1);}if(document.getElementById('videoModal').style.display==='flex'&&e.key==='Escape')closeVideoModal();});

// Form JS
document.querySelectorAll('.ppreset__btn').forEach(function(b){
    b.addEventListener('click',function(){
        document.querySelectorAll('.ppreset__btn').forEach(function(x){x.classList.remove('ppreset__btn--active')});
        this.classList.add('ppreset__btn--active');
        document.getElementById('donationAmount').value=this.getAttribute('data-a');
    });
});
document.getElementById('recurringToggle')?.addEventListener('change',function(){
    document.getElementById('recurringOptions').style.display=this.checked?'block':'none';
});
var pmSelect=document.getElementById('paymentMethodSelect'),cs=document.getElementById('cryptoSection'),cc=document.getElementById('cryptoCurrencySelect'),cn=document.getElementById('cryptoNetworkSelect'),cg=document.getElementById('cryptoNetworkGroup');
if(pmSelect){function tc(){var s=pmSelect.options[pmSelect.selectedIndex],d=s?s.getAttribute('data-driver'):'';cs.style.display=d==='crypto'?'block':'none';cg.style.display='none';cc.innerHTML='<option value=""><?php echo e(__('donate.choose_crypto')); ?></option>';if(d==='crypto'&&window.cryptoCurrencies)window.cryptoCurrencies.forEach(function(c){var o=document.createElement('option');o.value=c.id;o.textContent=c.name+' ('+c.symbol+')';o.setAttribute('data-networks',JSON.stringify(c.networks));cc.appendChild(o)});}
pmSelect.addEventListener('change',tc);}
if(cc){cc.addEventListener('change',function(){var s=this.options[this.selectedIndex],n=s?JSON.parse(s.getAttribute('data-networks')||'[]'):[];cn.innerHTML='<option value=""><?php echo e(__('donate.choose_network')); ?></option>';cg.style.display=n.length?'block':'none';n.forEach(function(n){var o=document.createElement('option');o.value=n.id;o.textContent=n.name;cn.appendChild(o)});});}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\etelaf-relief-laravel\resources\views/projects/show.blade.php ENDPATH**/ ?>