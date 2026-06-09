@extends('layouts.app')

@section('content')

{{-- Hero --}}
<section class="volunteer-hero">
    <div class="container">
        <div class="volunteer-hero__inner">
            <div class="volunteer-hero__content">
                <span class="section-tag">{{ __('volunteer.nav') }}</span>
                <h1>{{ __('volunteer.title') }}</h1>
                <p>{{ __('volunteer.subtitle') }}</p>
                <div class="volunteer-hero__stats">
                    <div class="hero-stat">
                        <span class="hero-stat__number">{{ $opportunities->count() }}</span>
                        <span class="hero-stat__label">{{ __('volunteer.opportunities_title') }}</span>
                    </div>
                </div>
            </div>
            <div class="volunteer-hero__visual">
                <div class="volunteer-hero__icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Form + Benefits --}}
<section class="section volunteer-main">
    <div class="container">
        <div class="volunteer-layout {{ $isRtl ? 'volunteer-layout--rtl' : 'volunteer-layout--ltr' }}">
            {{-- Form --}}
            <div class="volunteer-form-wrap">
                <div class="form-card">
                    <div class="form-card__header">
                        <i class="fas fa-pen"></i>
                        <h3>{{ __('volunteer.submit') }}</h3>
                    </div>
                    <form action="{{ route('volunteer.store', ['locale' => $currentLocale]) }}" method="POST">
                        @csrf
<input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                        <div class="form-row">
                            <div class="form-group">
                                <label>{{ __('common.full_name') }} <span class="required">*</span></label>
                                <input type="text" name="name" required placeholder="{{ __('common.full_name') }}">
                            </div>
                            <div class="form-group">
                                <label>{{ __('common.email') }} <span class="required">*</span></label>
                                <input type="email" name="email" required placeholder="{{ __('common.email') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>{{ __('common.phone') }} <span class="required">*</span></label>
                                <input type="tel" name="phone" required placeholder="{{ __('common.phone') }}">
                            </div>
                            <div class="form-group">
                                <label>{{ __('volunteer.national_id') }}</label>
                                <input type="text" name="national_id" placeholder="{{ __('volunteer.national_id') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>{{ __('volunteer.date_of_birth') }}</label>
                                <input type="date" name="date_of_birth">
                            </div>
                            <div class="form-group">
                                <label>{{ __('volunteer.address') }}</label>
                                <input type="text" name="address" placeholder="{{ __('volunteer.address') }}">
                            </div>
                        </div>
                        @if($opportunities->isNotEmpty())
                        <div class="form-group">
                            <label>{{ __('volunteer.select_opportunity') }}</label>
                            <select name="volunteer_opportunity_id">
                                <option value="">{{ __('volunteer.no_opportunity') }}</option>
                                @foreach($opportunities as $opp)
                                    <option value="{{ $opp->id }}">{{ trans_field($opp, 'title') }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group">
                            <label>{{ __('volunteer.skills') }}</label>
                            <textarea name="skills" rows="3" placeholder="{{ __('volunteer.skills') }}"></textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ __('volunteer.availability') }}</label>
                            <textarea name="availability" rows="3" placeholder="{{ __('volunteer.availability') }}"></textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ __('volunteer.emergency_contact') }}</label>
                            <div class="form-row">
                                <input type="text" name="emergency_contact_name" placeholder="{{ __('volunteer.emergency_name') }}">
                                <input type="tel" name="emergency_contact_phone" placeholder="{{ __('volunteer.emergency_phone') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('volunteer.message') }}</label>
                            <textarea name="message" rows="3" placeholder="{{ __('volunteer.message') }}"></textarea>
                        </div>
                        <button type="submit" class="btn btn--primary btn--lg btn--block">{{ __('volunteer.submit') }}</button>
                    </form>
                    <p class="form-card__footnote">
                        <i class="fas fa-info-circle"></i>
                        {{ __('volunteer.footnote') }}
                        <a href="{{ route('volunteer.dashboard', ['locale' => $currentLocale]) }}">{{ __('volunteer.check_status_link') }}</a>
                    </p>
                </div>
            </div>

            {{-- Benefits --}}
            <div class="volunteer-benefits">
                <div class="benefit-card benefit-card--highlight">
                    <div class="benefit-card__icon"><i class="fas fa-hands-helping"></i></div>
                    <h4>{{ __('volunteer.reason_1') }}</h4>
                </div>
                <div class="benefit-card">
                    <div class="benefit-card__icon"><i class="fas fa-user-graduate"></i></div>
                    <h4>{{ __('volunteer.reason_2') }}</h4>
                </div>
                <div class="benefit-card">
                    <div class="benefit-card__icon"><i class="fas fa-users"></i></div>
                    <h4>{{ __('volunteer.reason_3') }}</h4>
                </div>
                <div class="benefit-card">
                    <div class="benefit-card__icon"><i class="fas fa-heart"></i></div>
                    <h4>{{ __('volunteer.reason_4') }}</h4>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Opportunities --}}
@if($opportunities->isNotEmpty())
<section class="section opportunities-section">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title">{{ __('volunteer.opportunities_title') }}</h2>
        </div>
        <div class="opp-grid">
            @foreach($opportunities as $opp)
                <div class="opp-card">
                    <div class="opp-card__top">
                        <div class="opp-card__icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h3>{{ trans_field($opp, 'title') }}</h3>
                    </div>
                    <div class="opp-card__body">
                        <p>{{ trans_field($opp, 'description') }}</p>
                        @if($opp->requirements)
                            <div class="opp-card__req">
                                <strong>{{ __('volunteer.requirements') }}:</strong>
                                <span>{{ $opp->requirements }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="opp-card__footer">
                        @if($opp->location)
                            <span><i class="fas fa-map-marker-alt"></i> {{ $opp->location }}</span>
                        @endif
                        @if($opp->slots)
                            <span><i class="fas fa-users"></i> {{ $opp->slots }} {{ __('volunteer.slots') }}</span>
                        @endif
                        @if($opp->hours_required)
                            <span><i class="fas fa-clock"></i> {{ $opp->hours_required }} {{ __('volunteer.hours') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@push('head')
<style>
/* ── Hero ── */
.volunteer-hero {
    background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 100%);
    padding: 60px 0;
    color: #fff;
    overflow: hidden;
}
.volunteer-hero__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 3rem;
}
.volunteer-hero__content { flex: 1; }
.volunteer-hero__content h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #fff;
    margin: 1rem 0 0.75rem;
    line-height: 1.2;
}
.volunteer-hero__content > p {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.85);
    max-width: 540px;
    line-height: 1.7;
}
.volunteer-hero__stats { margin-top: 2rem; }
.hero-stat { display: inline-flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.12); padding: 0.75rem 1.5rem; border-radius: var(--radius-md); backdrop-filter: blur(4px); }
.hero-stat__number { font-size: 1.75rem; font-weight: 800; }
.hero-stat__label { font-size: 0.9rem; opacity: 0.85; }
.volunteer-hero__visual { flex-shrink: 0; }
.volunteer-hero__icon {
    width: 120px; height: 120px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem;
    backdrop-filter: blur(4px);
    border: 2px solid rgba(255,255,255,0.2);
}

/* ── Layout ── */
.volunteer-layout {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 3rem;
    align-items: start;
}
.volunteer-layout--rtl { direction: rtl; }
.volunteer-layout--ltr { direction: ltr; }

/* ── Form Card ── */
.form-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}
.form-card__header {
    background: var(--color-bg-alt);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 1px solid var(--color-border);
}
.form-card__header i { font-size: 1.25rem; color: var(--color-primary); }
.form-card__header h3 { font-size: 1.15rem; font-weight: 700; color: var(--color-text); margin: 0; }
.form-card form { padding: 1.5rem; }
.form-card__footnote {
    padding: 1rem 1.5rem;
    background: var(--color-bg-alt);
    border-top: 1px solid var(--color-border);
    font-size: 0.85rem;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.form-card__footnote a { color: var(--color-primary); font-weight: 600; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--color-text); }
.form-group .required { color: var(--color-danger); }
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-family: var(--font-family);
    font-size: 0.95rem;
    color: var(--color-text);
    background: var(--color-bg);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(13,107,79,0.1);
}
.form-group textarea { resize: vertical; min-height: 80px; }
.btn--block { width: 100%; }

/* ── Benefits ── */
.volunteer-benefits { display: flex; flex-direction: column; gap: 1rem; padding-top: 0.5rem; }
.benefit-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: var(--transition);
}
.benefit-card:hover { box-shadow: var(--shadow-sm); transform: translateX(4px); }
.volunteer-layout--rtl .benefit-card:hover { transform: translateX(-4px); }
.benefit-card--highlight {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-color: #a7f3d0;
}
.benefit-card__icon {
    width: 48px; height: 48px;
    background: var(--color-bg);
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    color: var(--color-primary);
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
}
.benefit-card h4 { font-size: 1rem; font-weight: 600; color: var(--color-text); margin: 0; line-height: 1.4; }

/* ── Opportunities ── */
.opportunities-section { background: var(--color-bg-alt); }
.opp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}
.opp-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition);
}
.opp-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }
.opp-card__top {
    padding: 1.5rem 1.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.opp-card__icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.1rem;
    flex-shrink: 0;
}
.opp-card__top h3 { font-size: 1.1rem; font-weight: 700; color: var(--color-text); margin: 0; }
.opp-card__body { padding: 0 1.5rem 1rem; }
.opp-card__body p { color: var(--color-text-muted); font-size: 0.9rem; line-height: 1.6; }
.opp-card__req {
    margin-top: 0.75rem;
    padding: 0.75rem;
    background: var(--color-bg-alt);
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
}
.opp-card__req strong { color: var(--color-text); }
.opp-card__req span { color: var(--color-text-muted); }
.opp-card__footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--color-border);
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.85rem;
    color: var(--color-text-muted);
    background: var(--color-bg-alt);
}
.opp-card__footer span { display: flex; align-items: center; gap: 0.35rem; }
.opp-card__footer i { color: var(--color-primary); }

/* ── Bottom CTA ── */
.volunteer-bottom-cta { padding-bottom: 80px; }
.bottom-cta-card {
    background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 100%);
    border-radius: var(--radius-lg);
    padding: 3rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    box-shadow: var(--shadow-lg);
}
.bottom-cta-card__content { flex: 1; }
.bottom-cta-card__content h2 { color: #fff; font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; }
.bottom-cta-card__content p { color: rgba(255,255,255,0.85); font-size: 1rem; }
.bottom-cta-card__actions { display: flex; gap: 1rem; flex-shrink: 0; }
.btn--outline-light { background: transparent; border: 2px solid rgba(255,255,255,0.4); color: #fff; }
.btn--outline-light:hover { background: rgba(255,255,255,0.12); border-color: #fff; color: #fff; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .volunteer-layout { grid-template-columns: 1fr; }
    .volunteer-benefits { display: grid; grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
    .volunteer-hero { padding: 40px 0; }
    .volunteer-hero__inner { flex-direction: column; text-align: center; }
    .volunteer-hero__content > p { max-width: 100%; }
    .volunteer-hero__visual { display: none; }
    .volunteer-hero__stats { display: flex; justify-content: center; }
    .volunteer-hero__content h1 { font-size: 1.75rem; }
    .form-row { grid-template-columns: 1fr; }
    .volunteer-benefits { grid-template-columns: 1fr; }
    .opp-grid { grid-template-columns: 1fr; }
    .bottom-cta-card { flex-direction: column; text-align: center; padding: 2rem 1.5rem; }
    .bottom-cta-card__actions { width: 100%; flex-direction: column; }
    .bottom-cta-card__actions .btn { width: 100%; }
}
</style>
@endpush
