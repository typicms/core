<x-core::layouts.auth :title="__('Register')">
    <div id="register" class="container-register auth">
        <x-core::auth-header />

        {!! BootForm::open()->addClass('auth-form')->id('registration-form') !!}

        <x-bootform::hidden name="locale" :value="app()->getLocale()" />

        <h1 class="auth-title">{{ __('Register') }}</h1>

        <x-core::status />

        <x-bootform::email :label="__('Email')" name="email" class="form-control-lg" required autocomplete="username" />
        <div class="row gx-3">
            <div class="col-sm-6"><x-bootform::text :label="__('First name')" name="first_name" class="form-control-lg" required /></div>
            <div class="col-sm-6"><x-bootform::text :label="__('Last name')" name="last_name" class="form-control-lg" required /></div>
        </div>

        <div class="mb-3 mt-3 d-grid"><x-bootform::submit :value="__('Register')" type="btn-primary" class="btn-lg" /></div>

        {!! BootForm::close() !!}
    </div>
</x-core::layouts.auth>
