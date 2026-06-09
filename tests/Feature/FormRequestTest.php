<?php

use App\Http\Requests\ChatActionRequest;
use App\Http\Requests\ChatStartRequest;
use App\Http\Requests\ConfirmationStoreRequest;
use App\Http\Requests\DonationRejectRequest;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::post('/test/send', function () {})->name('test.send');
    Route::post('/test/close', function () {})->name('test.close');
});

it('ChatStartRequest validates required fields', function () {
    $rules = (new ChatStartRequest)->rules();

    expect($rules)->toHaveKey('visitor_name')
        ->and($rules['visitor_name'])->toContain('required');
});

it('ChatStartRequest makes email optional', function () {
    $rules = (new ChatStartRequest)->rules();

    expect($rules['visitor_email'])->toContain('required')
        ->and($rules['visitor_email'])->toContain('email');
});

it('DonationRejectRequest allows nullable reason', function () {
    $rules = (new DonationRejectRequest)->rules();

    expect($rules['reason'])->toContain('nullable')
        ->and($rules['reason'])->toContain('max:2000');
});

it('ConfirmationStoreRequest validates file upload', function () {
    $rules = (new ConfirmationStoreRequest)->rules();

    expect($rules['proof_document'] ?? [])->toContain('file')
        ->and($rules['proof_document'] ?? [])->toContain('mimes:jpg,jpeg,png,pdf');
});

it('ChatActionRequest requires token for send method', function () {
    $rules = (new ChatActionRequest)->rules();

    expect($rules['session_id'])->toContain('required')
        ->and($rules['session_id'])->toContain('exists:chat_sessions,id');
});
