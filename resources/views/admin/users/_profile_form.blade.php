<div class="form-header form-header-bordered">
    <div class="form-header-top">
        <x-core::title :$model :default-title="__('Profile')" />
    </div>
    <div class="header-toolbar">
        <button class="btn btn-sm btn-primary" type="submit">{{ __('Save') }}</button>
    </div>
</div>

<div class="form-body">
    <x-core::form-errors />

    <div class="row gx-3">
        <div class="col-sm-6">{!! BootForm::text(__('First name'), 'first_name')->required()->autocomplete('off') !!}</div>
        <div class="col-sm-6">{!! BootForm::text(__('Last name'), 'last_name')->required()->autocomplete('off') !!}</div>
    </div>

    <div class="row gx-3">
        <div class="col-sm-6">{!! BootForm::email(__('Email'), 'email')->autocomplete('off')->required() !!}</div>
        <div class="col-sm-6">
            {!!
                BootForm::select(
                    __('Interface language'),
                    'locale',
                    collect(adminLocales())->mapWithKeys(fn(string $locale): array => [$locale => __('languages.' . $locale)])->all(),
                )->required()
            !!}
        </div>
    </div>

    <user-passkeys url-base="/api/users/{{ $model->id }}/passkeys" new-passkey-name="{{ auth()->user()->first_name }}'s passkey" :create-button="true"></user-passkeys>
</div>
