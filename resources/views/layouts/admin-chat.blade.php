<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('chat.admin_title') }} - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-chat.css') }}">
    @livewireStyles
</head>
<body>
    <div class="admin-chat-layout">
        {{ $slot }}
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('reverb.apps.default.app_key') }}',
            wsHost: '{{ config('reverb.apps.default.host') }}',
            wsPort: '{{ config('reverb.apps.default.port') }}',
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });
    </script>
    @livewireScripts
    <script src="{{ asset('js/admin-chat.js') }}"></script>
</body>
</html>
