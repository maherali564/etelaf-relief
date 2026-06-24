<div class="admin-chat" wire:key="admin-chat-panel">
    {{-- Sidebar --}}
    <aside class="chat-sidebar">
        <div class="chat-sidebar__header">
            <h2>{{ __('chat.admin_title') }}</h2>
            <div class="chat-sidebar__stats">
                <span class="badge badge--success">{{ __('chat.active') }}: <span x-text="$wire.sessions.filter(s => s.status === 'active').length">0</span></span>
                <span class="badge badge--warning">{{ __('chat.waiting') }}: <span x-text="$wire.sessions.filter(s => s.status === 'waiting').length">0</span></span>
            </div>
        </div>

        <div class="chat-sidebar__tabs">
            <button wire:click="$set('tab', 'active')" class="tab {{ $tab === 'active' ? 'tab--active' : '' }}">{{ __('chat.active') }}</button>
            <button wire:click="$set('tab', 'waiting')" class="tab {{ $tab === 'waiting' ? 'tab--active' : '' }}">{{ __('chat.waiting') }}
                @if($this->waitingCount > 0)<span class="tab__count">{{ $this->waitingCount }}</span>@endif
            </button>
            <button wire:click="$set('tab', 'closed')" class="tab {{ $tab === 'closed' ? 'tab--active' : '' }}">{{ __('chat.closed') }}</button>
        </div>

        <div class="chat-sidebar__search">
            <input type="text" wire:model.live="search" placeholder="{{ __('chat.search_placeholder') }}">
        </div>

        <div class="chat-sidebar__list">
            @forelse($this->filteredSessions as $session)
                <div
                    wire:key="session-{{ $session['id'] }}"
                    wire:click="selectSession({{ $session['id'] }})"
                    class="chat-session {{ $activeSessionId === $session['id'] ? 'chat-session--active' : '' }} {{ $session['status'] === 'waiting' ? 'chat-session--waiting' : '' }}"
                >
                    <div class="chat-session__avatar">
                        {{ strtoupper(substr($session['visitor_name'] ?? '?', 0, 1)) }}
                    </div>
                    <div class="chat-session__info">
                        <strong>{{ $session['visitor_name'] ?? __('chat.anonymous') }}</strong>
                        <small>{{ $session['visitor_email'] }}</small>
                        <span class="chat-session__preview">
                            {{ $session['last_message'] ?? '' }}
                        </span>
                    </div>
                    <div class="chat-session__meta">
                        <span class="status status--{{ $session['status'] }}">{{ __("chat.{$session['status']}") }}</span>
                        @if(($session['unread_count'] ?? 0) > 0)
                            <span class="unread-badge">{{ $session['unread_count'] }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="chat-empty">
                    <p>{{ __('chat.no_sessions') }}</p>
                </div>
            @endforelse
        </div>
    </aside>

    {{-- Main Chat Area --}}
    <main class="chat-main">
        @if($activeSessionId)
            <div class="chat-main__header">
                <div class="chat-main__visitor-info">
                    @php
                        $current = collect($sessions)->firstWhere('id', $activeSessionId);
                    @endphp
                    @if($current)
                        <strong>{{ $current['visitor_name'] ?? __('chat.anonymous') }}</strong>
                        <small>{{ $current['visitor_email'] }} · {{ $current['visitor_ip'] ?? '' }}</small>
                        @if($current['visitor_url'])
                            <small>{{ __('chat.page') }}: <a href="{{ $current['visitor_url'] }}" target="_blank" rel="noopener">{{ $current['visitor_url'] }}</a></small>
                        @endif
                    @endif
                </div>
                <button wire:click="closeSession" class="btn btn--danger btn--sm">{{ __('chat.end_chat') }}</button>
            </div>

            <div class="chat-main__messages" x-ref="adminMessages" @messages-loaded.window="$nextTick(() => $refs.adminMessages.scrollTop = $refs.adminMessages.scrollHeight)">
                @foreach($messages as $msg)
                    <div class="chat-msg {{ $msg['is_from_admin'] ? 'chat-msg--admin' : 'chat-msg--visitor' }}" wire:key="msg-{{ $msg['id'] }}">
                        <div class="chat-msg__bubble">
                            @if(!$msg['is_from_admin'] && isset($msg['user_name']))
                                <small class="chat-msg__name">{{ $msg['user_name'] }}</small>
                            @endif
                            {{ $msg['message'] }}
                        </div>
                        <span class="chat-msg__time">{{ \Carbon\Carbon::parse($msg['created_at'])->diffForHumans() }}</span>
                    </div>
                @endforeach

                <div x-show="$wire.isAdminTyping" class="chat-typing chat-typing--admin">
                    <span></span><span></span><span></span>
                    <small>{{ __('chat.visitor_typing') }}</small>
                </div>
            </div>

            <form wire:submit="sendMessage" class="chat-main__input">
                <input
                    type="text"
                    wire:model="message"
                    wire:keydown="typing"
                    placeholder="{{ __('chat.input_placeholder') }}"
                    required
                >
                <button type="submit" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </form>
        @else
            <div class="chat-main__empty">
                <svg viewBox="0 0 24 24" fill="currentColor" width="64" height="64" opacity="0.3">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/>
                </svg>
                <h3>{{ __('chat.select_session') }}</h3>
                <p>{{ __('chat.select_session_desc') }}</p>
            </div>
        @endif
    </main>
</div>
