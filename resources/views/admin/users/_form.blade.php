<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Users')" :default-title="__('New user')" :lang-switcher="false" />

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
        <div class="col">
            <x-bootform::email :label="__('Email')" name="email" autocomplete="off" required />
        </div>
        <div class="col">
            <x-bootform::text :label="__('Phone')" name="phone" autocomplete="off" />
        </div>
    </div>

    <div class="row gx-5">
        <div class="col">
            <x-bootform::text :label="__('Street')" name="street" />
        </div>
        <div class="md:col-2">
            <x-bootform::text :label="__('Number')" name="number" />
        </div>
        <div class="md:col-2">
            <x-bootform::text :label="__('Box')" name="box" />
        </div>
    </div>

    <div class="row gx-5">
        <div class="col">
            <x-bootform::text :label="__('Postal code')" name="postal_code" autocomplete="off" />
        </div>
        <div class="col">
            <x-bootform::text :label="__('City')" name="city" />
        </div>
        <div class="col">
            <x-bootform::text :label="__('Country')" name="country" autocomplete="off" />
        </div>
    </div>

    <div class="row gx-5">
        <div class="col-6 lg:col-2">
            <x-bootform::select
                :label="__('Interface language')"
                name="locale"
                :options="collect(adminLocales())->mapWithKeys(fn(string $locale): array => [$locale => __('languages.' . $locale)])->all()"
                required
            />
        </div>
    </div>

    <div class="mb-5">
        <x-bootform::checkbox :label="__('Activated')" name="activated" :unchecked-value="0" />
    </div>

    <div class="mb-5">
        <p class="form-label">{{ __('Roles') }}</p>
        @if (auth()->user()->isSuperUser())
            <x-bootform::checkbox :label="__('Superuser')" name="superuser" :unchecked-value="0" />
        @endif

        @if ($roles->count() > 0)
            @foreach ($roles as $role)
                <div class="form-field">
                    {!!
                        Form::checkbox('checked_roles[]', $role->id)
                            ->addClass('check')
                            ->id('role-' . $role->name)
                    !!}
                    <label for="{{ 'role-' . $role->name }}">{{ $role->name }}</label>
                </div>
            @endforeach
        @endif
    </div>

    @if ($model->id)
        <user-passkeys
            url-base="/api/users/{{ $model->id }}/passkeys"
            new-passkey-name="{{ auth()->user()->first_name . '’s passkey' }}"
            :create-button="{{ $model->id === auth()->id() ? 'true' : 'false' }}"
        ></user-passkeys>
    @endif
</div>
