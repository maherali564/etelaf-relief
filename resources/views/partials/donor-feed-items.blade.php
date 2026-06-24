@forelse($donations as $donation)
<div class="donor-entry">
    <div class="donor-entry__avatar">
        {{ strtoupper(substr($donation->is_anonymous ? __('common.anonymous') : $donation->donor_name, 0, 1)) }}
    </div>
    <div class="donor-entry__info">
        <strong class="donor-entry__name">{{ $donation->is_anonymous ? __('common.anonymous') : $donation->donor_name }}</strong>
        <span class="donor-entry__meta">
            @if($donation->project)
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
<p class="text-muted" style="padding:1rem;text-align:center;color:rgba(255,255,255,0.5);">{{ __('donor_wall.no_donations') }}</p>
@endforelse
