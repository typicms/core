<x-core::layouts.auth :title="__('Register')">
    <div id="register" class="container-register auth">
        <x-core::auth-header />

        {!! BootForm::open()->addClass('auth-form')->id('registration-form') !!}
        {!! BootForm::hidden('locale')->value(app()->getLocale()) !!}

        <h1 class="auth-title">{{ __('Register') }}</h1>

        <x-core::status />

        {!! BootForm::email(__('Email'), 'email')->addClass('form-control-lg')->required()->autocomplete('username') !!}
        <div class="row gx-3">
            <div class="col-sm-6">
                {!! BootForm::text(__('First name'), 'first_name')->addClass('form-control-lg')->required() !!}
            </div>
            <div class="col-sm-6">
                {!! BootForm::text(__('Last name'), 'last_name')->addClass('form-control-lg')->required() !!}
            </div>
        </div>

        <div class="mb-3 mt-3 d-grid">
            {!! BootForm::submit(__('Register'), 'btn-primary')->addClass('btn-lg') !!}
        </div>

        {!! BootForm::close() !!}
    </div>
</x-core::layouts.auth>
