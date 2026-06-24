<?php

use App\Models\ChatSession;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    $session = ChatSession::find($sessionId);
    if (! $session) {
        return false;
    }

    if ($user) {
        return $user->can('view', $session)
            || (int) $session->assigned_to === (int) $user->id;
    }

    return false;
});

Broadcast::channel('admin-chats', function ($user) {
    return $user->can_chat || $user->isSuperAdmin();
});
