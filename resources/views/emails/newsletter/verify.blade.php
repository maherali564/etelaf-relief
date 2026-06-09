@component('mail::message')
# {{ __('common.newsletter_verify_title') }}

{{ __('common.newsletter_verify_body') }}

@component('mail::button', ['url' => $verifyUrl ?? route('newsletter.verify', ['token' => $subscriber->verify_token, 'locale' => app()->getLocale()])])
{{ __('common.newsletter_verify_button') }}
@endcomponent

{{ __('common.newsletter_verify_ignore') }}

{{ __('common.thanks') }},<br>
{{ config('app.name') }}
@endcomponent
