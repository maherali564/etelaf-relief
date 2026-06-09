@php
    $totalRaised = $latestDonations->sum('amount');
    $totalDonors = $latestDonations->unique('email')->count();
@endphp
<section class="donor-wall section" id="donorWall">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title">{{ __('donor_wall.title') }}</h2>
            <p>{{ __('donor_wall.subtitle') }}</p>
        </div>

        <div class="donor-wall__layout">
            <div class="donor-wall__feed">
                <h3><i class="fas fa-clock"></i> {{ __('donor_wall.recent_donations') }}</h3>
                <div class="donor-feed" id="donorFeed">
                    @forelse($latestDonations as $donation)
                    <div class="donor-entry">
                        <div class="donor-entry__avatar">
                            {{ strtoupper(substr($donation->is_anonymous ? __('common.anonymous') : $donation->donor_name, 0, 1)) }}
                        </div>
                        <div class="donor-entry__info">
                            <strong class="donor-entry__name">{{ $donation->is_anonymous ? __('common.anonymous') : $donation->donor_name }}</strong>
                            <span class="donor-entry__meta">
                                @if($donation->campaign)
                                    {{ trans_field($donation->campaign, 'title') }}
                                @elseif($donation->project)
                                    {{ trans_field($donation->project, 'title') }}
                                @elseif($donation->story)
                                    {{ trans_field($donation->story, 'title') }}
                                @else
                                    {{ __('donate.general_donation') }}
                                @endif
                                · {{ $donation->donated_at?->diffForHumans() ?: $donation->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <span class="donor-entry__amount">${{ number_format($donation->amount, 0) }}</span>
                    </div>
                    @empty
                    <p class="text-muted">{{ __('donor_wall.no_donations') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="donor-wall__stats">
                <div class="donor-stat">
                    <span class="donor-stat__number" id="totalRaised" data-target="{{ number_format($totalRaised, 0, '', '') }}">$0</span>
                    <span class="donor-stat__label">{{ __('donor_wall.total_raised') }}</span>
                </div>
                <div class="donor-stat">
                    <span class="donor-stat__number" id="totalDonors" data-target="{{ $totalDonors }}">0</span>
                    <span class="donor-stat__label">{{ __('donor_wall.total_donors') }}</span>
                </div>
                <div class="donor-stat">
                    <span class="donor-stat__number" id="totalDonations" data-target="{{ $latestDonations->count() }}">0</span>
                    <span class="donor-stat__label">{{ __('donor_wall.total_donations') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="volunteer-cta section" id="volunteer">
    <div class="container">
        <div class="volunteer-cta__box">
            <div class="volunteer-cta__content">
                <h2>{{ __('volunteer.cta_title') }}</h2>
                <p>{{ __('volunteer.cta_desc') }}</p>
            </div>
            <div class="volunteer-cta__actions">
                <a href="{{ route('volunteer.register', ['locale' => $currentLocale]) }}" class="btn btn--primary btn--lg">{{ __('volunteer.cta_btn') }}</a>
                <a href="{{ route('volunteer.dashboard', ['locale' => $currentLocale]) }}" class="btn btn--outline btn--lg">{{ __('volunteer.my_dashboard') }}</a>
            </div>
        </div>
    </div>
</section>

<section class="contact section" id="contact">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title">{{ __('common.contact_us') }}</h2>
        </div>
        <div class="contact__grid {{ $isRtl ? 'contact__grid--rtl' : 'contact__grid--ltr' }}">
            <form class="contact-form {{ $isRtl ? 'contact-form--rtl' : 'contact-form--ltr' }}" action="{{ route('contact.store', ['locale' => $currentLocale]) }}" method="POST">
                @csrf
<input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

                <label><span>{{ __('common.full_name') }}</span><input type="text" name="name" required></label>
                <label><span>{{ __('common.email') }}</span><input type="email" name="email" required></label>
                <label><span>{{ __('common.subject') }}</span><input type="text" name="subject" required></label>
                <label><span>{{ __('common.message') }}</span><textarea name="message" rows="5" required></textarea></label>
                <button type="submit" class="btn btn--primary">{{ __('common.send_message') }}</button>
            </form>
            <div class="contact-info {{ $isRtl ? 'contact-info--rtl' : 'contact-info--ltr' }}">
                @if($s->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $s->whatsapp) }}" class="contact-info__item" target="_blank" rel="noopener">
                    <span class="contact-info__icon"><i class="fab fa-whatsapp"></i></span>
                    <div><strong>{{ __('common.whatsapp') }}</strong><span>{{ $s->whatsapp }}</span></div>
                </a>
                @endif
                @if($s->email)
                <a href="mailto:{{ $s->email }}" class="contact-info__item">
                    <span class="contact-info__icon"><i class="fas fa-envelope"></i></span>
                    <div><strong>{{ __('common.email') }}</strong><span>{{ $s->email }}</span></div>
                </a>
                @endif
                @if($s->phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $s->phone) }}" class="contact-info__item">
                    <span class="contact-info__icon"><i class="fas fa-phone"></i></span>
                    <div><strong>{{ __('common.phone') }}</strong><span>{{ $s->phone }}</span></div>
                </a>
                @endif
            </div>
        </div>
    </div>
</section>
