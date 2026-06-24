@extends('layouts.app')

@section('content')

<section class="hero-detailed" style="background:linear-gradient(135deg,#065f46,#059669)">
    <div class="hero-detailed__overlay" style="background:linear-gradient(180deg,rgba(0,0,0,.5),rgba(0,0,0,.25))"></div>
    <div class="hero-detailed__inner">
        <span class="hero-detailed__tag"><i class="fas fa-hands-helping"></i> {{ __('volunteer.nav') }}</span>
        <h1 class="hero-detailed__title">{{ __('volunteer.title') }}</h1>
        <p class="hero-detailed__desc">{{ __('volunteer.subtitle') }}</p>
        <div class="hero-detailed__stats">
            <div class="hero-detailed__stat"><span class="hero-detailed__stat-value">{{ $opportunities->count() }}</span><span class="hero-detailed__stat-label">{{ __('volunteer.opportunities_title') }}</span></div>
        </div>
    </div>
</section>

<section class="section" style="padding:3rem 0">
    <div class="container">
        <div class="vol-row">

            <div class="vol-col vol-col--form">
                <div class="vcard">
                    <div class="vcard__header">
                        <div class="vcard__icon"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <h2 class="vcard__title">{{ __('volunteer.submit') }}</h2>
                            <p class="vcard__sub">{{ __('volunteer.footnote') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('volunteer.store', ['locale' => $currentLocale]) }}" method="POST">
                        @csrf
                        <input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

                        <div class="vcard__section">
                            <h3 class="vcard__section-title">{{ __('volunteer.personal_info') }}</h3>
                            <div class="vcard__grid vcard__grid--2">
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('common.full_name') }} <span class="vfield__req">*</span></label>
                                    <input class="vfield__input" type="text" name="name" required placeholder="{{ __('common.full_name') }}">
                                </div>
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('common.email') }} <span class="vfield__req">*</span></label>
                                    <input class="vfield__input" type="email" name="email" required placeholder="example@domain.com">
                                </div>
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('common.phone') }} <span class="vfield__req">*</span></label>
                                    <input class="vfield__input" type="tel" name="phone" required placeholder="05xxxxxxxx">
                                </div>
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('volunteer.national_id') }}</label>
                                    <input class="vfield__input" type="text" name="national_id" placeholder="{{ __('volunteer.national_id') }}">
                                </div>
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('volunteer.date_of_birth') }}</label>
                                    <input class="vfield__input" type="text" name="date_of_birth" onfocus="this.type='date'" onblur="if(!this.value)this.type='text'" placeholder="{{ __('volunteer.date_of_birth_placeholder') }}">
                                </div>
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('volunteer.address') }}</label>
                                    <input class="vfield__input" type="text" name="address" placeholder="{{ __('volunteer.address') }}">
                                </div>
                            </div>
                        </div>

                        @if($opportunities->isNotEmpty())
                        <div class="vcard__section">
                            <h3 class="vcard__section-title">{{ __('volunteer.select_opportunity') }}</h3>
                            <div class="vfield">
                                <select class="vfield__input" name="volunteer_opportunity_id">
                                    <option value="">{{ __('volunteer.no_opportunity') }}</option>
                                    @foreach($opportunities as $opp)
                                        <option value="{{ $opp->id }}">{{ trans_field($opp, 'title') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="vcard__section">
                            <h3 class="vcard__section-title">{{ __('volunteer.skills_availability') }}</h3>
                            <div class="vcard__grid vcard__grid--2">
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('volunteer.skills') }}</label>
                                    <textarea class="vfield__input vfield__input--area" name="skills" rows="3" placeholder="{{ __('volunteer.skills_placeholder') }}"></textarea>
                                </div>
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('volunteer.availability') }}</label>
                                    <textarea class="vfield__input vfield__input--area" name="availability" rows="3" placeholder="{{ __('volunteer.availability_placeholder') }}"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="vcard__section">
                            <h3 class="vcard__section-title">{{ __('volunteer.emergency_contact') }}</h3>
                            <div class="vcard__grid vcard__grid--2">
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('volunteer.emergency_name') }}</label>
                                    <input class="vfield__input" type="text" name="emergency_contact_name" placeholder="{{ __('volunteer.emergency_name') }}">
                                </div>
                                <div class="vfield">
                                    <label class="vfield__label">{{ __('volunteer.emergency_phone') }}</label>
                                    <input class="vfield__input" type="tel" name="emergency_contact_phone" placeholder="{{ __('volunteer.emergency_phone') }}">
                                </div>
                            </div>
                        </div>

                        <div class="vcard__section">
                            <h3 class="vcard__section-title">{{ __('volunteer.message') }}</h3>
                            <div class="vfield">
                                <textarea class="vfield__input vfield__input--area" name="message" rows="4" placeholder="{{ __('volunteer.message_placeholder') }}"></textarea>
                            </div>
                        </div>

                        <div style="padding:1.25rem 1.75rem 1.75rem">
                            <button type="submit" class="vbtn"><i class="fas fa-paper-plane"></i> {{ __('volunteer.submit') }}</button>
                            <p class="vfootnote">
                                <i class="fas fa-info-circle"></i>
                                {{ __('volunteer.footnote_text') }}
                                <a href="{{ route('volunteer.dashboard', ['locale' => $currentLocale]) }}" class="vfootnote__link">{{ __('volunteer.check_status_link') }}</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <div class="vol-col vol-col--side">
                <div class="vcard" style="margin-bottom:1.25rem">
                    <div class="vcard__header">
                        <div class="vcard__icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#2563eb"><i class="fas fa-gem"></i></div>
                        <div>
                            <h2 class="vcard__title" style="font-size:1rem">{{ __('volunteer.why_volunteer') }}</h2>
                        </div>
                    </div>
                    <div style="padding:0 1.75rem 1.75rem">
                        @foreach([['icon'=>'hands-helping','text'=>'reason_1'],['icon'=>'user-graduate','text'=>'reason_2'],['icon'=>'users','text'=>'reason_3'],['icon'=>'heart','text'=>'reason_4']] as $reason)
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9">
                            <div style="width:36px;height:36px;background:#ecfdf5;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;color:#059669;flex-shrink:0"><i class="fas fa-{{ $reason['icon'] }}"></i></div>
                            <p style="margin:0;font-size:0.88rem;color:#475569;line-height:1.5">{{ __('volunteer.'.$reason['text']) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="vcard" style="text-align:center">
                    <div style="padding:1.75rem">
                        <div style="font-size:2.5rem;color:#059669;margin-bottom:0.5rem"><i class="fas fa-hand-holding-heart"></i></div>
                        <h3 style="margin:0 0 0.25rem;font-size:1rem;font-weight:700;color:#1e293b">{{ __('volunteer.join_today') }}</h3>
                        <p style="margin:0;font-size:0.85rem;color:#64748b">{{ __('volunteer.join_today_desc') }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@if($opportunities->isNotEmpty())
<section class="section" style="background:#f8fafc;padding:3rem 0">
    <div class="container">
        <h2 class="section-title" style="text-align:center;margin-bottom:2.5rem;font-size:1.5rem">
            <i class="fas fa-hands-helping" style="color:#059669;margin-left:8px"></i> {{ __('volunteer.opportunities_title') }}
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem">
            @foreach($opportunities as $opp)
            <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06);transition:all .3s ease;border:1px solid #e2e8f0" onmouseover="this.style.boxShadow='0 8px 25px rgba(0,0,0,.08)';this.style.borderColor='#a7f3d0'" onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.06)';this.style.borderColor='#e2e8f0'">
                <div style="padding:1.25rem">
                    <h4 style="font-size:1rem;font-weight:700;color:#059669;margin:0 0 8px">{{ trans_field($opp, 'title') }}</h4>
                    <p style="font-size:0.85rem;color:#64748b;line-height:1.6;margin-bottom:12px">{{ trans_field($opp, 'description') }}</p>
                    @if($opp->requirements)
                    <div style="font-size:0.8rem;background:#f1f5f9;padding:8px 12px;border-radius:8px;margin-bottom:12px;color:#475569">
                        <strong>{{ __('volunteer.requirements') }}:</strong> {{ $opp->requirements }}
                    </div>
                    @endif
                    <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:0.82rem;color:#64748b">
                        @if($opp->location)<span style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;padding:3px 10px;border-radius:20px;color:#059669"><i class="fas fa-map-marker-alt" style="font-size:0.7rem"></i> {{ $opp->location }}</span>@endif
                        @if($opp->slots)<span style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;padding:3px 10px;border-radius:20px;color:#059669"><i class="fas fa-users" style="font-size:0.7rem"></i> {{ $opp->slots }} {{ __('volunteer.slots') }}</span>@endif
                        @if($opp->hours_required)<span style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;padding:3px 10px;border-radius:20px;color:#059669"><i class="fas fa-clock" style="font-size:0.7rem"></i> {{ $opp->hours_required }} {{ __('volunteer.hours') }}</span>@endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<style>
.vol-row{display:flex;gap:1.75rem;align-items:flex-start}
.vol-col--form{flex:1.6;min-width:0}
.vol-col--side{flex:1;min-width:0}
.vcard{background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #e2e8f0;overflow:hidden}
.vcard__header{display:flex;align-items:center;gap:14px;padding:1.25rem 1.75rem;border-bottom:1px solid #f1f5f9}
.vcard__icon{width:44px;height:44px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;color:#059669;flex-shrink:0}
.vcard__title{margin:0;font-size:1.1rem;font-weight:700;color:#1e293b}
.vcard__sub{margin:2px 0 0;font-size:0.82rem;color:#94a3b8}
.vcard__section{padding:1.25rem 1.75rem;border-bottom:1px solid #f1f5f9}
.vcard__section:last-of-type{border-bottom:none}
.vcard__section-title{font-size:0.78rem;font-weight:700;color:#059669;margin:0 0 12px;display:flex;align-items:center;gap:6px}
.vcard__section-title::before{content:'';width:3px;height:14px;background:#059669;border-radius:2px}
.vcard__grid{display:grid;gap:12px}
.vcard__grid--2{grid-template-columns:1fr 1fr}
.vfield{margin-bottom:0}
.vfield__label{display:block;margin-bottom:5px;font-size:0.78rem;font-weight:600;color:#374151}
.vfield__req{color:#ef4444}
.vfield__input{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.88rem;background:#fafafa;transition:all .25s ease;box-sizing:border-box;color:#1e293b;font-family:inherit}
.vfield__input:hover{border-color:#cbd5e1}
.vfield__input:focus{outline:none;border-color:#10b981;background:#fff;box-shadow:0 0 0 4px rgba(16,185,129,.1)}
.vfield__input--area{resize:vertical;min-height:72px;line-height:1.5}
.vbtn{display:inline-flex;align-items:center;gap:8px;padding:13px 36px;font-size:1rem;font-weight:600;color:#fff;background:linear-gradient(135deg,#059669,#10b981);border:none;border-radius:12px;cursor:pointer;transition:all .3s ease;box-shadow:0 4px 14px rgba(5,150,105,.3);width:100%;justify-content:center;font-family:inherit}
.vbtn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(5,150,105,.4)}
.vbtn:active{transform:translateY(0)}
.vfootnote{margin:14px 0 0;font-size:0.82rem;color:#94a3b8;display:flex;align-items:center;gap:6px;justify-content:center}
.vfootnote__link{color:#059669;font-weight:600;text-decoration:none}
.vfootnote__link:hover{text-decoration:underline}
@media(max-width:900px){.vol-row{flex-direction:column-reverse}.vol-col--side{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}.vcard__grid--2{grid-template-columns:1fr}}
@media(max-width:550px){.vol-col--side{grid-template-columns:1fr}}
</style>
@endsection