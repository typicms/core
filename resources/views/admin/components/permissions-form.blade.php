{{-- Keep this component anonymous: ModuleServiceProvider registers the app’s
     component path before the one of the core, so a published copy overrides
     this file. A class component would resolve first and shadow it. --}}
@use(TypiCMS\Modules\Core\Support\Permissions)

@foreach (Permissions::alwaysGranted() as $permission => $label)
    <input type="hidden" name="checked_permissions[]" value="{{ $permission }}" />
@endforeach

<h2 class="my-7">{{ __('Global permissions') }}</h2>

<div class="mb-5">
    @foreach (Permissions::globals() as $permission => $label)
        <div class="form-field">
            {!!
                Form::checkbox('checked_permissions[]', $permission)
                    ->id('permission-' . Str::slug($permission))
                    ->addClass('check')
            !!}
            <label for="permission-{{ Str::slug($permission) }}">{{ __($label) }}</label>
        </div>
    @endforeach
</div>

<div class="permissions-modules">
    <h2 class="my-7">{{ __('Modules permissions') }}</h2>
    <div class="permissions-modules-items">
        @foreach (Permissions::grouped() as $module => $permissions)
            <div class="permissions-modules-item mt-3 mb-7">
                <label class="permissions-modules-item-title">{{ $module }}</label>
                @foreach ($permissions as $permission => $label)
                    <div class="permissions-modules-item-checkbox checkbox">
                        <div class="form-field">
                            {!!
                                Form::checkbox('checked_permissions[]', $permission)
                                    ->id('permission-' . Str::slug($permission))
                                    ->addClass('check')
                            !!}
                            <label for="permission-{{ Str::slug($permission) }}">{{ __($label) }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
