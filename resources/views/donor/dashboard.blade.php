@extends('layouts.app')

@section('title', __('donor.dashboard_title'))
@section('meta_description', __('donor.dashboard_desc'))

@section('content')
<section class="section">
    <div class="container">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;margin-bottom:2rem">
            <div>
                <h1 style="font-size:1.6rem;font-weight:700">{{ __('donor.dashboard_title') }}</h1>
                <p style="color:var(--color-text-muted)">{{ __('donor.welcome', ['name' => $donor->name]) }}</p>
            </div>
            <a href="{{ route('donor.logout', ['locale' => $currentLocale]) }}" onclick="event.preventDefault();document.getElementById('logout-form').submit()" style="padding:8px 20px;border:1px solid var(--color-border);border-radius:8px;color:var(--color-text);text-decoration:none;font-size:0.85rem">
                <i class="fas fa-sign-out-alt"></i> {{ __('donor.logout') }}
            </a>
            <form id="logout-form" action="{{ route('donor.logout', ['locale' => $currentLocale]) }}" method="POST" style="display:none">@csrf</form>
        </div>

        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2.5rem">
            <div style="padding:20px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:12px;text-align:center">
                <div style="font-size:1.8rem;font-weight:800;color:var(--color-primary)">${{ number_format($totalDonated, 0) }}</div>
                <div style="font-size:0.85rem;color:var(--color-text-muted);margin-top:4px">{{ __('donor.total_donated') }}</div>
            </div>
            <div style="padding:20px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:12px;text-align:center">
                <div style="font-size:1.8rem;font-weight:800;color:var(--color-primary)">{{ $donationCount }}</div>
                <div style="font-size:0.85rem;color:var(--color-text-muted);margin-top:4px">{{ __('donor.donation_count') }}</div>
            </div>
        </div>

        {{-- Donations table --}}
        <h2 style="font-size:1.2rem;font-weight:600;margin-bottom:1rem">{{ __('donor.your_donations') }}</h2>

        @if($donations->count() === 0)
        <div style="padding:3rem;text-align:center;background:var(--color-bg);border:1px solid var(--color-border);border-radius:12px">
            <i class="fas fa-heart" style="font-size:2rem;color:var(--color-text-muted);margin-bottom:1rem;display:block"></i>
            <p style="color:var(--color-text-muted)">{{ __('donor.no_donations') }}</p>
            <a href="{{ route('donate.page', ['locale' => $currentLocale]) }}" style="display:inline-block;margin-top:1rem;padding:10px 24px;background:var(--color-primary);color:#fff;border-radius:8px;text-decoration:none;font-weight:600">{{ __('donate_now') }}</a>
        </div>
        @else
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:0.9rem">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-border)">
                        <th style="padding:10px 8px;text-align:{{ $isRtl ? 'right' : 'left' }}">{{ __('donor.date') }}</th>
                        <th style="padding:10px 8px;text-align:{{ $isRtl ? 'right' : 'left' }}">{{ __('common.amount') }}</th>
                        <th style="padding:10px 8px;text-align:{{ $isRtl ? 'right' : 'left' }}">{{ __('common.cause') }}</th>
                        <th style="padding:10px 8px;text-align:{{ $isRtl ? 'right' : 'left' }}">{{ __('donor.status') }}</th>
                        <th style="padding:10px 8px;text-align:{{ $isRtl ? 'right' : 'left' }}">{{ __('donor.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donations as $d)
                    <tr style="border-bottom:1px solid var(--color-border)">
                        <td style="padding:10px 8px">{{ $d->donated_at?->format('Y/m/d') ?: $d->created_at->format('Y/m/d') }}</td>
                        <td style="padding:10px 8px;font-weight:600" data-amount="{{ $d->amount }}">${{ number_format($d->amount, 0) }}</td>
                        <td style="padding:10px 8px">
                            @if($d->project) {{ trans_field($d->project, 'title') }}
                            @elseif($d->story) {{ trans_field($d->story, 'title') }}
                            @elseif($d->post) {{ trans_field($d->post, 'title') }}
                            @else --
                            @endif
                        </td>
                        <td style="padding:10px 8px">
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;background:{{ $d->status === 'completed' ? '#d1fae5' : ($d->status === 'pending' ? '#fef3c7' : '#fee2e2') }};color:{{ $d->status === 'completed' ? '#065f46' : ($d->status === 'pending' ? '#92400e' : '#991b1b') }}">
                                {{ __('donor.status_' . $d->status) }}
                            </span>
                        </td>
                        <td style="padding:10px 8px">
                            @if($d->status === 'completed')
                            <div style="display:flex;gap:6px;flex-wrap:wrap">
                                <a href="{{ route('payment.certificate', ['locale' => $currentLocale, 'donation' => $d->id]) }}" style="padding:6px 12px;background:var(--color-primary);color:#fff;border-radius:6px;text-decoration:none;font-size:0.8rem">
                                    <i class="fas fa-award"></i> {{ __('donor.certificate') }}
                                </a>
                                <a href="{{ route('payment.tax_invoice', ['locale' => $currentLocale, 'donation' => $d->id]) }}" style="padding:6px 12px;background:#065f46;color:#fff;border-radius:6px;text-decoration:none;font-size:0.8rem">
                                    <i class="fas fa-file-invoice"></i> {{ __('tax_invoice.download') }}
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:1.5rem">{{ $donations->links() }}</div>
        @endif
    </div>
</section>
@endsection
