<section class="hero-slider" id="home">
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide hero-slide" style="--slide-bg: linear-gradient(135deg, #0d6b4f, #083b2b)">
                <div class="hero-slide__overlay"></div>
                <div class="container hero-slide__content">
                    <h1 class="hero-slide__title">{{ trans_field($s, 'hero_title') }}</h1>
                    <p class="hero-slide__subtitle">{{ trans_field($s, 'hero_subtitle') }}</p>
                    <a href="{{ route('donate.page', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--lg">{{ __('common.donate_now') }}</a>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next" style="left:20px!important;right:auto!important"></div>
        <div class="swiper-button-prev" style="right:20px!important;left:auto!important"></div>
    </div>
</section>

<section style="padding:20px 0;background:var(--color-bg-alt)">
    <div class="container">
        <div style="display:flex;flex-wrap:wrap;gap:16px;justify-content:center;align-items:center">
            <span style="font-size:0.8rem;color:var(--color-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:1px">{{ __('common.trust_badges') }}</span>
            <div style="width:1px;height:24px;background:var(--color-border)"></div>
            <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center">
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:20px;font-size:0.8rem;font-weight:600"><i class="fas fa-shield-alt" style="color:var(--color-primary)"></i> SSL Secured</span>
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:20px;font-size:0.8rem;font-weight:600"><i class="fas fa-lock" style="color:var(--color-primary)"></i> {{ __('common.pci_compliant') }}</span>
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:20px;font-size:0.8rem;font-weight:600"><i class="fas fa-check-circle" style="color:var(--color-primary)"></i> {{ __('common.audited') }}</span>
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:20px;font-size:0.8rem;font-weight:600"><i class="fas fa-hand-holding-heart" style="color:var(--color-primary)"></i> {{ __('common.registered_charity') }}</span>
            </div>
        </div>
    </div>
</section>

<section class="stats stats--achievements">
    <div class="container">
        <div class="stats__grid">
            @foreach($achievementStats as $stat)
            <div class="stat-item">
                <span class="stat-item__number" data-count="{{ $stat->value }}" data-prefix="{{ $stat->prefix ?? '' }}">0</span>
                <span class="stat-item__label">{{ trans_field($stat, 'label') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="stats stats--humanitarian">
    <div class="container">
        <h2 class="section-title section-title--light">{{ __('home.humanitarian_stats') }}</h2>
        <div class="stats__grid stats__grid--6">
            @foreach($humanitarianStats as $stat)
            <div class="stat-item stat-item--dark">
                <span class="stat-item__number" data-count="{{ $stat->value }}" data-prefix="{{ $stat->prefix ?? '' }}">0</span>
                <span class="stat-item__label">{{ trans_field($stat, 'label') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>
