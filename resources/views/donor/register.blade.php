@extends('layouts.app')

@section('title', __('donor.register_title'))
@section('meta_description', __('donor.register_subtitle'))

@section('content')
<section class="section">
    <div class="container" style="max-width:480px">
        <h1 style="font-size:1.6rem;font-weight:700;margin-bottom:0.5rem;text-align:center">{{ __('donor.register_title') }}</h1>
        <p style="color:var(--color-text-muted);text-align:center;margin-bottom:2rem">{{ __('donor.register_subtitle') }}</p>

        @if($errors->any())
        <div class="alert alert--error" style="margin-bottom:1.5rem">
            <ul style="margin:0;padding-left:1.2rem">
                @foreach($errors->all() as $error)
                <li style="font-size:0.85rem">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('donor.register.post', ['locale' => $currentLocale]) }}" style="display:flex;flex-direction:column;gap:1rem">
            @csrf
<input type="text" name="hp_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
            <div class="form-group">
                <label>{{ __('donor.full_name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--color-border);border-radius:8px;font-size:0.95rem">
            </div>
            <div class="form-group">
                <label>{{ __('common.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--color-border);border-radius:8px;font-size:0.95rem">
            </div>
            <div class="form-group">
                <label>{{ __('common.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--color-border);border-radius:8px;font-size:0.95rem">
            </div>
            <div class="form-group">
                <label>{{ __('donor.password') }}</label>
                <input type="password" name="password" required minlength="8" class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--color-border);border-radius:8px;font-size:0.95rem">
            </div>
            <div class="form-group">
                <label>{{ __('donor.password_confirm') }}</label>
                <input type="password" name="password_confirmation" required class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--color-border);border-radius:8px;font-size:0.95rem">
            </div>
            <button type="submit" style="padding:12px 24px;background:var(--color-primary);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer">
                {{ __('donor.register_btn') }}
            </button>
        </form>
        <p style="text-align:center;margin-top:1.5rem;font-size:0.9rem">
            {{ __('donor.have_account') }} <a href="{{ route('donor.login', ['locale' => $currentLocale]) }}" style="color:var(--color-primary)">{{ __('donor.login_link') }}</a>
        </p>
    </div>
</section>
@endsection
