<?php

use App\Models\ChatSession;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    if ($user) {
        return true;
    }

    return Gate::allows('view', ChatSession::find($sessionId));
});

Broadcast::channel('admin-chats', function ($user) {
    return $user->can_chat || $user->isSuperAdmin();
});
