<x-core::layouts.auth :title="__('Login')">
    <div id="login" class="container-login auth auth-sm">
        <x-core::auth-header />

        {!! BootForm::open()->action(route(app()->getLocale() . '::send-one-time-password'))->addClass('auth-form') !!}

        <h1 class="auth-title">{{ __('Login') }}</h1>

        <x-core::status />

        {!! BootForm::email(__('Email'), 'email')->addClass('form-control-lg')->autofocus(true)->required()->autocomplete('username') !!}

        <div class="mb-3 d-grid">
            {!! BootForm::submit(__('Send'), 'btn-primary')->addClass('btn-lg') !!}
        </div>

        <a class="text-body text-decoration-underline small text-center mt-3 d-block" href="{{ route(app()->getLocale() . '::login') }}">@lang('Authenticate with a passkey.')</a>
        {!! BootForm::close() !!}

        <x-core::register-info />

        <x-core::back-to-website-link />
    </div>
</x-core::layouts.auth>
