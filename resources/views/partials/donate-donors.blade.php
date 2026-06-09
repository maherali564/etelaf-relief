@if($donations->isNotEmpty())
<div class="donate-project__donors">
    <h3><i class="fas fa-users" style="color:#059669"></i> {{ __('donor_wall.recent_donations') }}</h3>
    <div class="donors-list">
        @foreach($donations as $donation)
        <div class="donors-list__item">
            <div class="donors-list__avatar" style="background:linear-gradient(135deg,#059669,#10b981)">
                {{ strtoupper(substr($donation->is_anonymous ? __('common.anonymous') : $donation->donor_name, 0, 1)) }}
            </div>
            <div class="donors-list__info">
                <span class="donors-list__name">{{ $donation->is_anonymous ? __('common.anonymous') : $donation->donor_name }}</span>
                <span class="donors-list__date">{{ $donation->donated_at?->diffForHumans() ?: $donation->created_at->diffForHumans() }}</span>
            </div>
            <span class="donors-list__amount" style="color:#059669">${{ number_format($donation->amount, 0) }}</span>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="donate-project__donors donate-project__donors--empty">
    <div style="text-align:center;padding:2rem;color:#94a3b8">
        <i class="fas fa-heart" style="font-size:2rem;color:#d1d5db;margin-bottom:0.75rem;display:block"></i>
        <p>{{ __('donor_wall.no_donations') }}</p>
    </div>
</div>
@endif
