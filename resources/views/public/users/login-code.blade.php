<x-core::layouts.auth :title="__('Login')">
    <div id="login" class="container-login auth auth-sm">
        <x-core::auth-header />

        {!! BootForm::open()->action(route(app()->getLocale() . '::submit-one-time-password'))->addClass('auth-form') !!}

        <h1 class="auth-title">{{ __('Enter your one-time password') }}</h1>

        <x-core::status />

        <x-bootform::text :label="__('One time password')" name="one_time_password" class="form-control-lg" autofocus required autocomplete="one-time-code" />

        <div class="mb-3 d-grid"><x-bootform::submit :value="__('Submit')" type="btn-primary" class="btn-lg" /></div>

        {!! BootForm::close() !!}

        <x-core::back-to-website-link />
    </div>
</x-core::layouts.auth>
