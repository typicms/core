@if (config('typicms.registration.allowed'))
    <p class="alert-not-a-member">
        {{ __('Not a member?') }}
        <a class="alert-link" href="{{ route(app()->getLocale() . '::register') }}">{{ __('Become a member') }}</a>
        {{ __('and get access to all the content of our website.') }}
    </p>
@endif
