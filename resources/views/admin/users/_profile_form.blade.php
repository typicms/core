<div class="form-header form-header-bordered">
    <div class="form-header-top">
        <x-core::title :$model :default-title="__('Profile')" />
    </div>
    <div class="header-toolbar">
        <button class="btn-sm btn-solid theme-primary" type="submit">{{ __('Save') }}</button>
    </div>
</div>

<div class="form-body">
    <x-core::form-errors />

    <div class="row gx-5">
        <div class="sm:col-6">
            <x-bootform::text :label="__('First name')" name="first_name" required autocomplete="off" />
        </div>
        <div class="sm:col-6">
            <x-bootform::text :label="__('Last name')" name="last_name" required autocomplete="off" />
        </div>
    </div>

    <div class="row gx-5">
        <div class="sm:col-6">
            <x-bootform::email :label="__('Email')" name="email" autocomplete="off" required />
        </div>
        <div class="sm:col-6">
            <x-bootform::select
                :label="__('Interface language')"
                name="locale"
                :options="collect(adminLocales())->mapWithKeys(fn(string $locale): array => [$locale => __('languages.' . $locale)])->all()"
                required
            />
        </div>
    </div>

    <user-passkeys url-base="/api/users/{{ $model->id }}/passkeys" new-passkey-name="{{ auth()->user()->first_name }}'s passkey" :create-button="true"></user-passkeys>
</div>
