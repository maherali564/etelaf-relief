@if($testimonials->isNotEmpty())
<section class="section" style="background:var(--color-bg-alt)">
    <div class="container">
        <div class="section-header section-header--center">
            <span class="section-tag">{{ __('common.testimonials_title') }}</span>
            <h2 class="section-title">{{ __('common.testimonials_title') }}</h2>
            <p>{{ __('common.testimonials_desc') }}</p>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:24px;justify-content:center">
            @foreach($testimonials as $testimonial)
            <article class="testimonial-card">
                @if($testimonial->image)
                <img loading="lazy" src="{{ asset('storage/'.$testimonial->image) }}" alt="" class="testimonial-card__avatar">
                @else
                <div class="testimonial-card__avatar testimonial-card__avatar--initials">{{ strtoupper(substr($testimonial->donor_name, 0, 1)) }}</div>
                @endif
                <h3 class="testimonial-card__name">{{ $testimonial->donor_name }}</h3>
                @if($testimonial->rating)
                <div class="testimonial-card__rating">
                    @for($i=0;$i<5;$i++)
                        <i class="fas fa-star{{ $i<$testimonial->rating ? '' : '-o' }}"></i>
                    @endfor
                </div>
                @endif
                <p class="testimonial-card__text">"{{ trans_field($testimonial, 'content') }}"</p>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($faqs->isNotEmpty())
<section class="faq section">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title">{{ __('common.nav_faq') }}</h2>
        </div>
        <div class="faq__list">
            @foreach($faqs as $faq)
            <details class="faq__item">
                <summary class="faq__question">{{ trans_field($faq, 'question') }}</summary>
                <div class="faq__answer">{!! nl2br(e(trans_field($faq, 'answer'))) !!}</div>
            </details>
            @endforeach
        </div>
    </div>
</section>
@endif
