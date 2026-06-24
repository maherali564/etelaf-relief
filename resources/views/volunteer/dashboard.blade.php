@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header section-header--center">
            <h2 class="section-title">{{ __('volunteer.dashboard') }}</h2>
        </div>

        @if($volunteer)
            <div class="volunteer-status">
                <div class="card">
                    <h3>{{ __('volunteer.application_status') }}</h3>
                    <p class="status-badge status-badge--{{ $volunteer->status }}">
                        @switch($volunteer->status)
                            @case('approved') {{ __('volunteer.status_approved') }} @break
                            @case('rejected') {{ __('volunteer.status_rejected') }} @break
                            @default {{ __('volunteer.status_pending') }}
                        @endswitch
                    </p>
                    @if($volunteer->status === 'approved' && $volunteer->approved_at)
                        <p><small>{{ __('volunteer.approved_date') }}: {{ $volunteer->approved_at->format('Y-m-d') }}</small></p>
                    @endif
                </div>

                <div class="card">
                    <h3>{{ __('volunteer.your_info') }}</h3>
                    <p><strong>{{ __('common.full_name') }}:</strong> {{ $volunteer->name }}</p>
                    <p><strong>{{ __('common.email') }}:</strong> {{ $volunteer->email }}</p>
                    <p><strong>{{ __('common.phone') }}:</strong> {{ $volunteer->phone }}</p>
                    @if($volunteer->skills)
                        <p><strong>{{ __('volunteer.skills') }}:</strong> {{ $volunteer->skills }}</p>
                    @endif
                    @if($volunteer->availability)
                        <p><strong>{{ __('volunteer.availability') }}:</strong> {{ $volunteer->availability }}</p>
                    @endif
                </div>

                @if($volunteer->tasks->isNotEmpty())
                    <div class="card">
                        <h3>{{ __('volunteer.my_tasks') }}</h3>
                        <div class="tasks-list">
                            @foreach($volunteer->tasks as $task)
                                <div class="task-item">
                                    <strong>{{ $task->title }}</strong>
                                    <span class="status-badge status-badge--{{ $task->status }}">{{ $task->status }}</span>
                                    @if($task->hours_logged)
                                        <span>{{ $task->hours_logged }} {{ __('volunteer.hours') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p><strong>{{ __('volunteer.total_hours') }}:</strong> {{ $volunteer->totalHours() }}</p>
                    </div>
                @endif
            </div>
        @else
            <div class="card">
                <p>{{ __('volunteer.check_status_hint') }}</p>
                <form action="{{ route('volunteer.dashboard', ['locale' => $currentLocale]) }}" method="GET" class="volunteer-check-form">
                    <label>
                        <span>{{ __('common.email') }}</span>
                        <input type="email" name="email" required placeholder="{{ __('volunteer.email_placeholder') }}">
                    </label>
                    <button type="submit" class="btn btn--primary">{{ __('volunteer.check_status') }}</button>
                </form>
            </div>
        @endif

        @if($opportunities->isNotEmpty())
            <div class="opportunities">
                <h3>{{ __('volunteer.opportunities_title') }}</h3>
                <div class="opportunities__grid">
                    @foreach($opportunities as $opp)
                        <div class="opportunity-card">
                            <h4>{{ trans_field($opp, 'title') }}</h4>
                            <p>{{ trans_field($opp, 'description') }}</p>
                            @if($opp->requirements)
                                <p><small><strong>{{ __('volunteer.requirements') }}:</strong> {{ $opp->requirements }}</small></p>
                            @endif
                            <div class="opportunity-card__meta">
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
        @endif
    </div>
</section>
@endsection

@push('head')
<style>
.volunteer-status { display: flex; flex-direction: column; gap: 1.5rem; max-width: 800px; margin: 0 auto; }
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; }
.card h3 { margin-bottom: 1rem; color: #1e293b; }
.status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; }
.status-badge--pending { background: #fef3c7; color: #92400e; }
.status-badge--approved { background: #d4edda; color: #155724; }
.status-badge--rejected { background: #fce4ec; color: #7f1d1d; }
.status-badge--assigned { background: #dbeafe; color: #1e40af; }
.status-badge--completed { background: #d4edda; color: #155724; }
.tasks-list { display: flex; flex-direction: column; gap: 0.75rem; }
.task-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: 8px; }
.opportunities__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
.opportunity-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; }
.opportunity-card h4 { color: #059669; margin-bottom: 0.5rem; }
.opportunity-card__meta { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; font-size: 0.85rem; color: #64748b; }
.volunteer-check-form { display: flex; gap: 1rem; align-items: flex-end; margin-top: 1rem; }
.volunteer-check-form label { flex: 1; }
.volunteer-check-form input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; }
</style>
@endpush
