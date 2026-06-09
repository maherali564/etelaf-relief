@extends('layouts.app')

@section('title', __('chat.admin_title'))

@section('content')
    <div class="admin-chat-wrapper">
        @livewire('admin-chat-panel')
    </div>
@endsection
