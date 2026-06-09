<?php

use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('starts a new chat session via service', function () {
    $service = new ChatService;
    $result = $service->startSession('Test User', 'test@example.com', '127.0.0.1', 'https://example.com');

    expect($result)->toHaveKeys(['session_id', 'token']);
    $this->assertDatabaseHas('chat_sessions', [
        'id' => $result['session_id'],
        'visitor_name' => 'Test User',
        'status' => 'waiting',
    ]);
    $this->assertDatabaseHas('chat_messages', [
        'chat_session_id' => $result['session_id'],
        'is_from_admin' => false,
    ]);
});

it('sends a visitor message', function () {
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');

    $msg = $service->sendMessage($session['session_id'], $session['token'], 'Hello!');

    expect($msg->message)->toBe('Hello!')
        ->and($msg->is_from_admin)->toBeFalse();
});

it('closes a chat session', function () {
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');

    $service->closeSession($session['session_id']);

    $this->assertDatabaseHas('chat_sessions', [
        'id' => $session['session_id'],
        'status' => 'closed',
    ]);
});

it('assigns a session to admin', function () {
    $admin = User::factory()->create();
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');

    $assigned = $service->assignSession($session['session_id'], $admin);

    expect($assigned->assigned_to)->toBe($admin->id);
    $this->assertDatabaseHas('chat_messages', [
        'chat_session_id' => $session['session_id'],
        'is_from_admin' => true,
    ]);
});

it('sends admin message in assigned session', function () {
    $admin = User::factory()->create();
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');
    $service->assignSession($session['session_id'], $admin);

    $msg = $service->sendAdminMessage($session['session_id'], $admin, 'Welcome!');

    expect($msg->message)->toBe('Welcome!')
        ->and($msg->is_from_admin)->toBeTrue()
        ->and($msg->user_id)->toBe($admin->id);
});

it('returns session messages for visitor', function () {
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');
    $service->sendMessage($session['session_id'], $session['token'], 'Message 1');

    $messages = $service->getVisitorMessages($session['session_id']);

    expect($messages)->toHaveCount(2);
});

it('closes session via admin', function () {
    $admin = User::factory()->create();
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');

    $service->closeAdminSession($session['session_id'], $admin);

    $this->assertDatabaseHas('chat_sessions', [
        'id' => $session['session_id'],
        'status' => 'closed',
    ]);
});

it('validates session ownership via token', function () {
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');

    $messages = ChatMessage::where('chat_session_id', $session['session_id'])->get();
    expect($messages->first()->visitor_token)->toBe($session['token']);
});

it('fails to send message with wrong session id', function () {
    $service = new ChatService;
    $session = $service->startSession('User', 'u@example.com', '127.0.0.1');

    expect(fn () => $service->sendMessage(99999, $session['token'], 'test'))
        ->toThrow(ModelNotFoundException::class);
});
