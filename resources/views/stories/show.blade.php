@extends('layouts.app')
@section('title', trans_field($story, 'title'))
@section('og_image', $story->first_image ? asset('storage/'.$story->first_image) : '')
@section('content')

@php
$allImages = $story->images ?? [];
if ($story->image && !in_array($story->image, $allImages)) {
    array_unshift($allImages, $story->image);
}
$allVideos = [];
if ($story->video_url) {
    $allVideos[] = ['url' => $story->video_url, 'type' => $story->video_type, 'thumbnail' => $story->video_thumbnail];
} elseif (!empty($story->videos)) {
    $storyVids = is_array($story->videos) ? $story->videos : json_decode($story->videos, true) ?? [];
    foreach ($storyVids as $v) {
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
$totalRaised = (int) ($story->raised_amount ?? 0);
$totalGoal = (int) ($story->goal_amount ?? 0);
$progressPct = $totalGoal > 0 ? min(100, round($totalRaised / $totalGoal * 100)) : 0;
$remaining = max(0, $totalGoal - $totalRaised);
$latestDonation = $donations->first();
$donorCount = $donations->pluck('donor_name')->unique()->filter()->count();
@endphp

@push('head')
<style>
.hero-detailed::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 100px;
    background: linear-gradient(to bottom, #fff, transparent);
    pointer-events: none; z-index: 2;
}
</style>
@endpush

<section class="hero-detailed" @if($story->first_image) style="background-image:url('{{ asset('storage/'.$story->first_image) }}')" @endif>
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-book-open"></i> {{ __('common.nav_stories') }}</span>
        <h1 class="hero-detailed__title">{{ trans_field($story, 'title') }}</h1>
        <p class="hero-detailed__desc">
            {{ trans_field($story, 'person_name') }}{{ trans_field($story, 'age') ? ', '.trans_field($story, 'age').' '.__('common.age') : '' }}{{ trans_field($story, 'location') ? ' | '.trans_field($story, 'location') : '' }}
        </p>
        @if($story->goal_amount > 0)
        <div class="hero-detailed__stats">
            <div class="hero-detailed__stat"><span class="hero-detailed__stat-value">${{ number_format($story->raised_amount ?? 0) }}</span><span class="hero-detailed__stat-label">{{ __('site.raised') }}</span></div>
            <div class="hero-detailed__stat"><span class="hero-detailed__stat-value">${{ number_format($story->goal_amount) }}</span><span class="hero-detailed__stat-label">{{ __('site.goal') }}</span></div>
            <div class="hero-detailed__stat"><span class="hero-detailed__stat-value">{{ $story->progressPercent() }}%</span><span class="hero-detailed__stat-label">{{ __('common.progress') }}</span></div>
        </div>
        <x-progress-bar :raised="$story->raised_amount ?? 0" :goal="$story->goal_amount" />
        @endif
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="donation-layout">

            {{-- Right: Media Gallery + Story Detail (60%) --}}
            <div class="gallery-container">

                <div class="main-image" id="mainImageWrap">
                    <img id="mainImage" class="main-img-visible" src="{{ asset('storage/'.$allImages[0]) }}" alt="" onclick="openLightbox(0)">
                    <img id="mainImageNext" class="main-img-hidden" src="" alt="" aria-hidden="true">
                </div>

                @if(count($allMedia) > 0)
                <div class="gallery-tabs">
                    <button class="gallery-tab gallery-tab--active" data-filter="all" onclick="filterMedia('all',this)">{{ __('common.all') ?? 'All' }}</button>
                    <button class="gallery-tab" data-filter="image" onclick="filterMedia('image',this)">{{ __('common.images') ?? 'Images' }}</button>
                    <button class="gallery-tab" data-filter="video" onclick="filterMedia('video',this)">{{ __('common.videos') ?? 'Videos' }}</button>
                </div>

                <div class="media-thumbs" id="mediaThumbs">
                    @foreach($allMedia as $m)
                    <button class="thumb-item {{ $loop->first ? 'thumb-item--active' : '' }} {{ $m['type'] === 'video' ? 'thumb-item--video' : '' }}"
                            data-media-type="{{ $m['type'] }}"
                            onclick="{{ $m['type'] === 'image' ? "setMainImage({$m['imgIndex']});openLightbox({$m['imgIndex']})" : "openVideoModal({$m['vidIndex']})" }}"
                            aria-label="{{ $m['type'] === 'image' ? 'Image '.($m['imgIndex']+1) : 'Video '.($m['vidIndex']+1) }}">
                        <img src="{{ $m['thumb'] }}" alt="" loading="lazy">
                        @if($m['type'] === 'video')
                        <span class="thumb-item__play"><i class="fas fa-play"></i></span>
                        @endif
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- Story Details Card --}}
                <div class="project-detail-card">
                    <h2 class="project-detail-card__title">{{ trans_field($story, 'title') }}</h2>

                    <div class="project-detail-card__meta">
                        @if(trans_field($story, 'person_name'))
                        <div class="project-detail-card__meta-row">
                            <i class="fas fa-user"></i>
                            <span>{{ trans_field($story, 'person_name') }}{{ trans_field($story, 'age') ? ', '.trans_field($story, 'age').' '.__('common.age') : '' }}</span>
                        </div>
                        @endif
                        @if(trans_field($story, 'location'))
                        <div class="project-detail-card__meta-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ trans_field($story, 'location') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="project-detail-card__desc">
                        {!! trans_field($story, 'content') !!}
                    </div>

                    @php $shareUrl = urlencode(url()->current()); $shareText = urlencode(trans_field($story, 'title')); @endphp
                    <div class="project-detail-card__share">
                        <a href="https://wa.me/?text={{ $shareText.'%0A'.$shareUrl }}" target="_blank" rel="noopener" class="share-circle share-circle--whatsapp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareUrl }}" target="_blank" rel="noopener" class="share-circle share-circle--twitter" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.facebook.com/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="share-circle share-circle--facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>

            </div>

            {{-- Left: Donation Form (40%) --}}
            <div class="form-container">
                <div class="pcard">
                    <div class="pcard__head">
                        <div class="pcard__head-icon"><i class="fas fa-hand-holding-heart"></i></div>
                        <div>
                            <h2 class="pcard__head-title">{{ __('common.contribute') }}</h2>
                            <p class="pcard__head-sub">{{ __('home.donate_desc') }}</p>
                        </div>
                    </div>

                    @if($totalGoal > 0)
                    <div class="pcard__progress">
                        <div class="pcard__stats">
                            <div class="pstat pstat--main"><i class="fas fa-hand-holding-heart"></i> <span>${{ number_format($totalRaised) }}</span> <small>{{ __('common.raised') }}</small></div>
                            <div class="pstat pstat--dim"><i class="fas fa-users"></i> <span>{{ number_format(max($donorCount, $donations->count())) }}</span> <small>{{ __('common.donors') }}</small></div>
                        </div>
                        <div class="pcard__bar-wrap"><div class="pcard__bar" style="width:{{ $progressPct }}%"></div></div>
                        <div class="pcard__pct">{{ $progressPct }}% {{ __('common.completed') }}</div>
                        @if($remaining > 0)
                        <div class="pcard__remain"><i class="fas fa-hourglass-half"></i> {{ __('common.remaining_goal', ['amount' => number_format($remaining)]) }}</div>
                        @endif
                    </div>
                    @endif

                    @if($latestDonation)
                    <div class="pcard__latest">
                        <i class="fas fa-bolt" style="color:#f59e0b"></i>
                        <strong>{{ $latestDonation->donor_name ?: __('common.anonymous') }}</strong> {{ __('common.donated') }}
                        <strong>${{ number_format($latestDonation->amount, 0) }}</strong>
                        <span>{{ $latestDonation->created_at->diffForHumans() }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('donate.store', $currentLocale) }}" class="pcard__form">
                        <input type="hidden" name="story_id" value="{{ $story->id }}">
                        <input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                        @yield('donate_entity_fields')

                        <div class="pfld">
                            <label class="pfld__label">{{ __('donate.quick_amounts') }}</label>
                            <div class="ppreset">
                                @foreach([10,25,50,100,250,500] as $p)
                                <button type="button" class="ppreset__btn" data-a="{{ $p }}">${{ $p }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="pfld">
                            <div class="pgrid pgrid--2">
                                <div><label class="pfld__label">{{ __('donate.custom_amount') }} <span style="color:#ef4444">*</span></label><input class="pi" type="number" name="amount" id="donationAmount" min="1" step="0.01" required placeholder="{{ __('donate.min_amount') }}"></div>
                                <div><label class="pfld__label">{{ __('common.full_name') }} <span style="color:#ef4444">*</span></label><input class="pi" type="text" name="donor_name" required placeholder="{{ __('common.full_name') }}"></div>
                                <div><label class="pfld__label">{{ __('common.email') }} <span style="color:#ef4444">*</span></label><input class="pi" type="email" name="email" required placeholder="example@domain.com"></div>
                                <div><label class="pfld__label">{{ __('common.phone') }}</label><input class="pi" type="tel" name="phone" placeholder="05xxxxxxxx"></div>
                            </div>
                        </div>

                        <div class="pfld">
                            <label class="pfld__label">{{ __('donate.payment_method') }}</label>
                            <select class="pi" name="payment_method_id" id="paymentMethodSelect" required>
                                <option value="">{{ __('donate.select_payment_method') }}</option>
                                @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}" data-driver="{{ $pm->gateway?->driver ?? '' }}">{{ $pm->name }} - {{ $pm->description }}</option>
                                @endforeach
                            </select>
                        </div>

                        @php $cryptoJson = $cryptocurrencies->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'symbol' => $c->symbol, 'networks' => $c->networks->map(fn($n) => ['id' => $n->id, 'name' => $n->network_name])]); @endphp
                        <script>window.cryptoCurrencies = {!! json_encode($cryptoJson) !!};</script>
                        <div id="cryptoSection" class="pfld" style="display:none">
                            <div class="pgrid pgrid--2">
                                <div><label class="pfld__label">{{ __('donate.select_crypto') }}</label><select class="pi" name="cryptocurrency_id" id="cryptoCurrencySelect"><option value="">{{ __('donate.choose_crypto') }}</option></select></div>
                                <div id="cryptoNetworkGroup" style="display:none"><label class="pfld__label">{{ __('donate.select_network') }}</label><select class="pi" name="crypto_network_id" id="cryptoNetworkSelect"><option value="">{{ __('donate.choose_network') }}</option></select></div>
                            </div>
                        </div>

                        <div class="pfld">
                            <label class="pcheck"><input type="checkbox" name="is_anonymous" value="1"> <span>{{ __('donate.anonymous_donation') }} <small>{{ __('donate.anonymous_hint') }}</small></span></label>
                            <label class="pcheck"><input type="checkbox" name="is_recurring" value="1" id="recurringToggle"> <span>{{ __('donate.recurring_donation') }}</span></label>
                        </div>

                        <div id="recurringOptions" class="pfld" style="display:none">
                            <label class="pfld__label">{{ __('donate.recurring_interval') }}</label>
                            <select class="pi" name="recurring_interval">
                                <option value="monthly">{{ __('donate.every_month') }}</option>
                                <option value="quarterly">{{ __('donate.every_3_months') }}</option>
                                <option value="yearly">{{ __('donate.every_year') }}</option>
                            </select>
                        </div>

                        <div class="pfld" style="margin-bottom:0">
                            <label class="pfld__label">{{ __('donate.donation_note') }}</label>
                            <textarea class="pi pi--area" name="notes" rows="2" placeholder="{{ __('donate.donation_note_placeholder') }}"></textarea>
                        </div>

                        <button type="submit" class="pbtn"><i class="fas fa-heart"></i> {{ __('common.complete_donation') }}</button>
                        <p class="psecure"><i class="fas fa-lock"></i> {{ __('donate.secure_notice') }}</p>
                    </form>

                </div>
            </div>

        </div>
    </div>
</section>

@if($donations->isNotEmpty())
<section class="section section--muted">
    <div class="container">
        <h2 class="section-title section-title--center">{{ __('donor_wall.recent_donations') }}</h2>
        <div class="donor-list">
            @foreach($donations as $d)
            <div class="donor-list__row">
                <span class="donor-list__avatar">{{ strtoupper(substr($d->is_anonymous ? __('common.anonymous') : $d->donor_name, 0, 1)) }}</span>
                <div class="donor-list__info">
                    <strong>{{ $d->is_anonymous ? __('common.anonymous') : $d->donor_name }}</strong>
                    <span class="donor-list__meta">{{ $d->donated_at?->diffForHumans() ?: $d->created_at->diffForHumans() }}</span>
                </div>
                <span class="donor-list__amount">${{ number_format($d->amount, 0) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(isset($similar) && $similar->isNotEmpty())
<section class="section">
    <div class="container">
        <h2 class="section-title section-title--center">{{ __('common.similar_stories') ?? 'قصص مشابهة' }}</h2>
        <div class="similar-grid">
            @foreach($similar as $s)
            <a href="{{ route('stories.show', ['locale' => $currentLocale, 'id' => $s->id]) }}" class="similar-card">
                @if($s->first_image)
                <div class="similar-card__image" style="background-image:url('{{ asset('storage/'.$s->first_image) }}')"></div>
                @endif
                <div class="similar-card__body">
                    <h4>{{ trans_field($s, 'title') }}</h4>
                    <x-progress-bar :raised="$s->raised_amount ?? 0" :goal="$s->goal_amount" />
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Lightbox --}}
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
.donation-layout { display: flex; flex-direction: row; gap: 40px; align-items: flex-start; }
.gallery-container { flex: 0 0 60%; min-width: 0; }
.form-container { flex: 0 0 calc(40% - 40px); min-width: 0; position: sticky; top: 100px; }
@media (max-width: 900px) {
    .donation-layout { flex-direction: column; }
    .gallery-container, .form-container { flex: 1; position: static; }
}
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
.hero-detailed__desc { color: rgba(255,255,255,.85); font-size: 1rem; margin-top: 4px; }

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

.gallery-tabs { display: flex; gap: 6px; margin-bottom: 12px; }
.gallery-tab {
    padding: 6px 16px; border-radius: 20px; border: 2px solid #e2e8f0;
    background: #fff; color: #64748b; font-size: .82rem; font-weight: 600; cursor: pointer;
    transition: all .2s ease;
}
.gallery-tab:hover { border-color: #10b981; color: #10b981; }
.gallery-tab--active { border-color: #10b981; background: #10b981; color: #fff; }

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
    padding: 12px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;
    margin-bottom: 12px;
}
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

.form-card { background: #fff; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,.1); border: 1px solid #e8e8ec; overflow: hidden; }
.form-card__header { background: linear-gradient(135deg, #059669, #10b981); padding: 14px 18px 12px; }
.form-card__title {
    font-size: .95rem; font-weight: 800; color: #fff; margin: 0;
    display: flex; align-items: center; gap: 8px;
}
.form-card__title::before {
    content: '\f004'; font-family: 'Font Awesome 6 Free','Font Awesome 5 Free','FontAwesome';
    font-weight: 900; font-size: 1.15rem; color: rgba(255,255,255,.9);
}
.form-card .donate-form { padding: 12px 18px 16px; border: none; box-shadow: none; }
.form-card .donate-form > h3 { display: none; }
.form-card .form-group { margin-bottom: 10px; }
.form-card label {
    display: block; margin-bottom: 4px; font-size: .7rem; font-weight: 700;
    color: #374151; text-transform: uppercase;
}
.form-card input:not([type="checkbox"]):not([type="radio"]),
.form-card select, .form-card textarea {
    width: 100%; padding: 8px 10px; border: 2px solid #e2e8f0; border-radius: 8px;
    font-size: .82rem; background: #fafafa; transition: all .25s ease;
    box-sizing: border-box; color: #1e293b;
}
.form-card input:not([type="checkbox"]):not([type="radio"]):hover,
.form-card select:hover { border-color: #cbd5e1; background: #fff; }
.form-card input:not([type="checkbox"]):not([type="radio"]):focus,
.form-card select:focus, .form-card textarea:focus {
    outline: none; border-color: #10b981; background: #fff; box-shadow: 0 0 0 3px rgba(16,185,129,.1);
}
.form-card textarea { resize: vertical; min-height: 54px; }

.form-card__progress { padding: 12px 18px 8px; border-bottom: 1px solid #f1f5f9; }
.form-card__bar-wrap { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 10px; }
.form-card__bar { height: 100%; background: linear-gradient(90deg, #059669, #10b981); border-radius: 4px; transition: width .5s ease; }
.form-card__stats { display: flex; gap: 8px; margin-bottom: 6px; }
.fstat { flex: 1; text-align: center; font-size: .72rem; color: #64748b; }
.fstat i { display: block; font-size: .9rem; color: #10b981; margin-bottom: 2px; }
.fstat span { display: block; font-size: .95rem; font-weight: 800; color: #1e293b; }
.fstat small { display: block; color: #94a3b8; font-size: .65rem; text-transform: uppercase; }
.form-card__remaining {
    font-size: .75rem; color: #f59e0b; font-weight: 700; text-align: center;
    padding-bottom: 2px; display: flex; align-items: center; justify-content: center; gap: 4px;
}

.form-card__latest {
    padding: 8px 18px; font-size: .75rem; color: #64748b;
    background: #fffbeb; border-bottom: 1px solid #fef3c7;
    display: flex; align-items: center; gap: 4px; flex-wrap: wrap;
}
.form-card__latest strong { color: #1e293b; }
.form-card__latest span { color: #94a3b8; font-size: .7rem; }

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

.similar-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.similar-card {
    background: #fff; border-radius: 12px; overflow: hidden;
    box-shadow: 0 3px 12px rgba(0,0,0,.06); border: 1px solid #f1f5f9;
    text-decoration: none; color: inherit; transition: all .3s ease; display: block;
}
.similar-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.1); }
.similar-card__image { height: 160px; background-size: cover; background-position: center; }
.similar-card__body { padding: 12px 16px; }
.similar-card__body h4 { font-size: .95rem; font-weight: 700; color: #1e293b; margin: 0 0 8px; }

.lightbox-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.92); z-index: 9999;
    display: flex; align-items: center; justify-content: center; animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.lightbox-overlay--video { z-index: 10000; }
.lightbox-close {
    position: absolute; top: 16px; {{ $isRtl ? 'left' : 'right' }}: 16px;
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
.lightbox-nav--prev { {{ $isRtl ? 'right' : 'left' }}: 16px; }
.lightbox-nav--next { {{ $isRtl ? 'left' : 'right' }}: 16px; }
.lightbox-image { max-width: 90%; max-height: 85%; object-fit: contain; border-radius: 6px; box-shadow: 0 8px 40px rgba(0,0,0,.5); }
.lightbox-counter { position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); color: rgba(255,255,255,.5); font-size: 13px; }

.video-wrapper { width: 90vw; max-width: 960px; height: 60vh; max-height: 80vh; display: flex; align-items: center; justify-content: center; }
.video-player { width: 100%; height: 100%; border-radius: 8px; box-shadow: 0 8px 40px rgba(0,0,0,.5); background: #000; }

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
var lightboxImages = {!! json_encode(array_map(fn($img) => asset('storage/'.$img), $allImages)) !!};
var vidUrls = {!! json_encode(array_map(fn($v) => $v['url'], $allVideos)) !!};
var vidTypes = {!! json_encode(array_map(fn($v) => $v['type'], $allVideos)) !!};
var vidEmbeds = {!! json_encode(array_map(fn($v) => ($v['type']==='youtube' ? 'https://www.youtube.com/embed/'.preg_replace('/[^a-zA-Z0-9_-]/','',$v['url']) : ($v['type']==='vimeo' ? 'https://player.vimeo.com/video/'.intval(preg_replace('/[^0-9]/','',$v['url'])) : '')), $allVideos)) !!};
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
</script>
@endsection