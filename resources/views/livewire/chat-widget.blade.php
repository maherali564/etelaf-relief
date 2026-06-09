<div>
    <button
        x-on:click="$wire.set('showChat', !$wire.showChat)"
        class="chat-fab"
        :class="{ 'chat-fab--active': $wire.showChat }"
        aria-label="{{ __('common.chat') }}">
        <svg class="chat-fab__icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/>
            <path d="M7 9h10v2H7zm0-3h10v2H7zm0 6h7v2H7z"/>
        </svg>
        @if($waitingSessions > 0)
            <span class="chat-fab__badge">{{ $waitingSessions }}</span>
        @endif
    </button>

    @if($showChat)
    <div class="chat-window" wire:key="chat-window">
        <div class="chat-header">
            <div class="chat-header__info">
                <h4>{{ __('common.chat') }}</h4>
                <span class="chat-header__status">
                    <span class="status-dot status-dot--online"></span>
                    @if($activeSessions > 0)
                        {{ $activeSessions }} {{ __('chat.active') }}
                    @else
                        {{ __('chat.online') }}
                    @endif
                </span>
            </div>
            <button x-on:click="$wire.set('showChat', false)" class="chat-header__close">
                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        </div>

        @if($step === 'prechat')
        <div class="chat-body">
            <div class="chat-prechat">
                <div class="chat-prechat__icon">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="48" height="48">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/>
                    </svg>
                </div>
                <h3>{{ __('chat.start_title') }}</h3>
                <p>{{ __('chat.start_desc') }}</p>
                <form wire:submit="startChat" class="chat-prechat__form">
                    <input type="text" wire:model="visitorName" placeholder="{{ __('chat.name_placeholder') }}" required>
                    @error('visitorName') <span class="chat-error">{{ $message }}</span> @enderror
                    <input type="email" wire:model="visitorEmail" placeholder="{{ __('chat.email_placeholder') }}" required>
                    @error('visitorEmail') <span class="chat-error">{{ $message }}</span> @enderror
                    <button type="submit" class="btn btn--primary btn--block">{{ __('chat.start_btn') }}</button>
                </form>
            </div>
        </div>
        @endif

        @if($step === 'chat')
        <div class="chat-body" wire:poll.5s="loadMessages">
            <div class="chat-messages" x-ref="msgArea" x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                @foreach($messages as $msg)
                    <div class="chat-msg {{ $msg['is_from_admin'] ? 'chat-msg--admin' : 'chat-msg--visitor' }}">
                        <div class="chat-msg__bubble">{{ $msg['message'] }}</div>
                        <span class="chat-msg__time">{{ \Carbon\Carbon::parse($msg['created_at'])->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>

            <form wire:submit="sendMessage" class="chat-input">
                <input type="text" wire:model="message" placeholder="{{ __('chat.input_placeholder') }}" required>
                <button type="submit" class="chat-input__send">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
                <button type="button" x-on:click="$wire.closeChat()" class="chat-input__end" title="{{ __('chat.end_chat') }}">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                    </svg>
                </button>
            </form>
        </div>
        @endif
    </div>
    @endif
</div>
