<!DOCTYPE html>
<?php
    $currentLocale = $currentLocale ?? app()->getLocale();
    $isRtl = $isRtl ?? ($currentLocale === 'ar');
    $supportedLocales = $supportedLocales ?? config('app.supported_locales', ['ar', 'en', 'es', 'id', 'tr']);
    $localeLabels = $localeLabels ?? ['ar' => 'العربية','en' => 'English','es' => 'Español','id' => 'Bahasa Indonesia','tr' => 'Türkçe'];
    $s = $settings ?? \App\Models\SiteSetting::current();
?>
<html lang="<?php echo e($currentLocale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', trans_field($s, 'site_name', $currentLocale) ?? config('app.name')); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', trans_field($s, 'tagline', $currentLocale) ?? ''); ?>">
    <meta property="og:title" content="<?php echo $__env->yieldContent('title', trans_field($s, 'site_name', $currentLocale) ?? config('app.name')); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('meta_description', trans_field($s, 'tagline', $currentLocale) ?? ''); ?>">
    <meta property="og:type" content="<?php echo $__env->yieldContent('og_type', 'website'); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('og_image', asset('storage/' . (($s->logos[$currentLocale] ?? $s->logo ?? '')))); ?>">
    <meta property="og:locale" content="<?php echo e($currentLocale === 'ar' ? 'ar_AR' : $currentLocale . '_' . strtoupper($currentLocale)); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">
    <script type="application/ld+json">
    { "@context": "https://schema.org", "@type": "NGO", "name": "<?php echo e(trans_field($s, 'site_name', $currentLocale) ?? config('app.name')); ?>", "description": "<?php echo e(trans_field($s, 'tagline', $currentLocale) ?? ''); ?>", "url": "<?php echo e(url('/')); ?>", "logo": "<?php echo e(asset('storage/' . (($s->logos[$currentLocale] ?? $s->logo ?? '')))); ?>", "foundingDate": "2024" }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&family=Playfair+Display:wght@400;500;700&family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/extra.css')); ?>?v=12">
    <link rel="stylesheet" href="<?php echo e(asset('css/chat.css')); ?>">
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?> <?php echo e($bodyClass ?? ''); ?>">
    <div id="devNotice" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#1c2e2b;text-align:center;padding:8px 16px;font-size:0.8rem;font-weight:600;position:sticky;top:0;z-index:10000;display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap">
        <span><?php echo e(__('common.dev_notice')); ?></span>
        <button onclick="localStorage.setItem('dev_notice_dismissed','1');this.parentElement.style.display='none'" style="padding:3px 12px;border:2px solid #1c2e2b;border-radius:999px;background:transparent;color:#1c2e2b;font-size:0.75rem;font-weight:700;cursor:pointer;white-space:nowrap"><?php echo e(__('common.dev_dismiss')); ?></button>
    </div>
    <script>if(localStorage.getItem('dev_notice_dismissed')){document.getElementById('devNotice').style.display='none'}</script>
    <?php echo $__env->make('partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert--success container"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <script src="<?php echo e(asset('js/main.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>

    <div id="cookieConsent" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:9999;padding:0 16px 16px;pointer-events:none" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
        <div style="max-width:512px;margin:0 auto;background:rgba(9,9,11,0.95);-webkit-backdrop-filter:blur(24px);backdrop-filter:blur(24px);border:1px solid rgba(63,63,70,0.8);border-radius:24px;padding:20px 24px 20px;box-shadow:0 25px 60px rgba(0,0,0,0.5);pointer-events:auto;position:relative;overflow:hidden;transition:opacity 0.4s ease,transform 0.4s cubic-bezier(0.16,1,0.3,1);opacity:0;transform:translateY(30px)" id="cookieInner">
            <div style="position:absolute;top:-40px;left:-40px;width:96px;height:96px;background:rgba(16,185,129,0.08);border-radius:50%;filter:blur(32px);pointer-events:none"></div>
            <div style="position:absolute;bottom:-40px;right:-40px;width:96px;height:96px;background:rgba(16,185,129,0.04);border-radius:50%;filter:blur(32px);pointer-events:none"></div>
            <div style="display:flex;align-items:flex-start;gap:12px">
                <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);padding:8px;border-radius:12px;color:#34d399;flex-shrink:0;margin-top:2px"><i class="fas fa-shield-halved" style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:16px"></i></div>
                <div style="flex:1;min-width:0">
                    <h4 style="margin:0 0 2px;font-size:14px;font-weight:800;color:#f4f4f5;letter-spacing:0.01em"><?php echo e(__('common.cookie_title')); ?></h4>
                    <div id="cookieDesc" style="font-size:12px;color:#d4d4d8;font-weight:500;line-height:1.6"><?php echo e(__('common.cookie_desc')); ?></div>
                </div>
            </div>
            <div id="cookiePanel" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid #18181b;max-height:260px;overflow-y:auto">
                <div style="display:flex;gap:12px;padding:12px;background:rgba(24,24,27,0.4);border:1px solid rgba(24,24,27,0.6);border-radius:12px;margin-bottom:10px;opacity:0.9;cursor:not-allowed">
                    <div style="flex-shrink:0;margin-top:2px;width:16px;height:16px;background:#059669;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-check" style="font-size:10px;stroke-width:3"></i></div>
                    <div style="min-width:0"><strong style="font-size:12px;font-weight:700;color:#f4f4f5;display:block"><?php echo e(__('common.cookie_necessary')); ?></strong><span style="font-size:11px;color:#a1a1aa;line-height:1.4;display:block"><?php echo e(__('common.cookie_necessary_desc')); ?></span></div>
                </div>
                <div onclick="cookieToggle('analytics')" style="display:flex;gap:12px;padding:12px;border:1px solid;border-radius:12px;cursor:pointer;user-select:none;transition:all 0.2s;margin-bottom:10px" id="cookieAnalyticsRow">
                    <div style="flex-shrink:0;margin-top:2px;width:16px;height:16px;border:1.5px solid #52525b;border-radius:4px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;background:transparent;color:transparent" id="cookieAnalyticsCheck"><i class="fas fa-check" style="font-size:10px;stroke-width:3"></i></div>
                    <div style="min-width:0"><strong style="font-size:12px;font-weight:700;color:#f4f4f5;display:block"><?php echo e(__('common.cookie_analytics')); ?></strong><span style="font-size:11px;color:#a1a1aa;line-height:1.4;display:block"><?php echo e(__('common.cookie_analytics_desc')); ?></span></div>
                </div>
                <div onclick="cookieToggle('marketing')" style="display:flex;gap:12px;padding:12px;border:1px solid;border-radius:12px;cursor:pointer;user-select:none;transition:all 0.2s" id="cookieMarketingRow">
                    <div style="flex-shrink:0;margin-top:2px;width:16px;height:16px;border:1.5px solid #52525b;border-radius:4px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;background:transparent;color:transparent" id="cookieMarketingCheck"><i class="fas fa-check" style="font-size:10px;stroke-width:3"></i></div>
                    <div style="min-width:0"><strong style="font-size:12px;font-weight:700;color:#f4f4f5;display:block"><?php echo e(__('common.cookie_marketing')); ?></strong><span style="font-size:11px;color:#a1a1aa;line-height:1.4;display:block"><?php echo e(__('common.cookie_marketing_desc')); ?></span></div>
                </div>
            </div>
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid #18181b">
                <a href="<?php echo e(route('pages.show', ['locale' => $currentLocale, 'slug' => 'privacy-policy'])); ?>" style="font-size:11px;font-weight:700;color:#34d399;text-decoration:underline;display:inline-flex;align-items:center;gap:4px;transition:color 0.2s" onmouseover="this.style.color='#6ee7b7'" onmouseout="this.style.color='#34d399'"><?php echo e(__('common.cookie_privacy')); ?> <i class="fas fa-arrow-<?php echo e($isRtl ? 'left' : 'right'); ?>" style="font-size:10px"></i></a>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap" id="cookieActions">
                    <button onclick="cookieShowPrefs()" style="padding:8px 12px;background:rgba(24,24,27,0.8);border:1px solid rgba(39,39,42,0.8);border-radius:12px;color:#d4d4d8;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.2s" onmouseover="this.style.background='#27272a';this.style.color='#fff'" onmouseout="this.style.background='rgba(24,24,27,0.8)';this.style.color='#d4d4d8'" id="cookiePrefsBtn"><i class="fas fa-gear" style="font-size:12px;margin-<?php echo e($isRtl ? 'left' : 'right'); ?>:4px"></i> <?php echo e(__('common.cookie_more')); ?></button>
                    <button onclick="cookieEssential()" style="padding:8px 12px;background:transparent;border:1px solid rgba(39,39,42,0.6);border-radius:12px;color:#a1a1aa;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.2s" onmouseover="this.style.borderColor='#52525b';this.style.color='#e4e4e7'" onmouseout="this.style.borderColor='rgba(39,39,42,0.6)';this.style.color='#a1a1aa'"><?php echo e(__('common.cookie_decline')); ?></button>
                    <button onclick="cookieAcceptAll()" style="padding:8px 16px;background:#059669;border:none;border-radius:12px;color:#fff;font-size:11px;font-weight:900;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 12px rgba(5,150,105,0.2)" onmouseover="this.style.background='#10b981'" onmouseout="this.style.background='#059669'" id="cookieAcceptBtn"><?php echo e(__('common.cookie_accept')); ?></button>
                </div>
                <div style="display:none;align-items:center;gap:8px" id="cookiePrefsActions">
                    <button onclick="cookieHidePrefs()" style="padding:8px 12px;background:transparent;border:1px solid #18181b;border-radius:12px;color:#a1a1aa;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.2s" onmouseover="this.style.color='#e4e4e7'" onmouseout="this.style.color='#a1a1aa'"><?php echo e($isRtl ? '→' : '←'); ?> <?php echo e(__('common.cookie_back')); ?></button>
                    <button onclick="cookieSavePrefs()" style="padding:8px 16px;background:#059669;border:none;border-radius:12px;color:#fff;font-size:11px;font-weight:900;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 12px rgba(5,150,105,0.2)" onmouseover="this.style.background='#10b981'" onmouseout="this.style.background='#059669'"><?php echo e(__('common.cookie_save')); ?></button>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function(){var K='sahem_cookie_consent',c=document.getElementById('cookieConsent'),i=document.getElementById('cookieInner');if(!c||!i)return;var prefs={essential:true,analytics:true,marketing:true};function save(){localStorage.setItem(K,JSON.stringify({essential:true,analytics:prefs.analytics,marketing:prefs.marketing,timestamp:new Date().toISOString()}));i.style.opacity='0';i.style.transform='translateY(30px)';setTimeout(function(){c.style.display='none'},400)}function show(){c.style.display='block';setTimeout(function(){i.style.opacity='1';i.style.transform='translateY(0)'},50)}var saved=localStorage.getItem(K);if(!saved){setTimeout(show,1000)}else{try{var p=JSON.parse(saved);prefs.analytics=p.analytics!==false;prefs.marketing=p.marketing!==false}catch(e){}}window.cookieAcceptAll=function(){prefs.analytics=true;prefs.marketing=true;save()};window.cookieEssential=function(){prefs.analytics=false;prefs.marketing=false;save()};window.cookieShowPrefs=function(){document.getElementById('cookieDesc').style.display='none';document.getElementById('cookiePanel').style.display='block';document.getElementById('cookieActions').style.display='none';document.getElementById('cookiePrefsActions').style.display='flex';syncPrefsUI()};window.cookieHidePrefs=function(){document.getElementById('cookieDesc').style.display='block';document.getElementById('cookiePanel').style.display='none';document.getElementById('cookieActions').style.display='flex';document.getElementById('cookiePrefsActions').style.display='none'};window.cookieToggle=function(k){if(k==='essential')return;prefs[k]=!prefs[k];syncPrefsUI()};window.cookieSavePrefs=function(){save()};function syncPrefsUI(){function setRow(id,checkId,val){var r=document.getElementById(id),ck=document.getElementById(checkId);if(!r||!ck)return;if(val){r.style.background='rgba(24,24,27,0.4)';r.style.borderColor='rgba(16,185,129,0.2)';ck.style.background='#059669';ck.style.borderColor='#059669';ck.style.color='#fff'}else{r.style.background='transparent';r.style.borderColor='#18181b';ck.style.background='transparent';ck.style.borderColor='#52525b';ck.style.color='transparent'}}setRow('cookieAnalyticsRow','cookieAnalyticsCheck',prefs.analytics);setRow('cookieMarketingRow','cookieMarketingCheck',prefs.marketing)}})()
    </script>
</body>
</html>
<?php /**PATH D:\etelaf-relief-laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>