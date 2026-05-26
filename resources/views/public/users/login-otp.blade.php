<x-core::layouts.auth :title="__('Login')">
    <div id="login" class="container-login auth auth-sm">
        <x-core::auth-header />

        {!! BootForm::open()->action(route(app()->getLocale() . '::send-one-time-password'))->addClass('auth-form') !!}

        <h1 class="auth-title">{{ __('Login') }}</h1>

        <x-core::status />

        <x-bootform::email :label="__('Email')" name="email" class="form-control-lg" autofocus required autocomplete="username" />

        <div class="mb-3 d-grid"><x-bootform::submit :value="__('Send')" type="btn-primary" class="btn-lg" /></div>

        <a class="text-body text-decoration-underline small text-center mt-3 d-block" href="{{ route(app()->getLocale() . '::login') }}">{{ __('Authenticate with a passkey.') }}</a>
        {!! BootForm::close() !!}

        <x-core::register-info />

        <x-core::back-to-website-link />
    </div>
</x-core::layouts.auth>
