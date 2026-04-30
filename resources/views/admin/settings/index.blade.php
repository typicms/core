<x-core::layouts.admin :title="__('Settings')">
    {!! BootForm::open()->addClass('form') !!}
    {!! BootForm::bind($data) !!}

    <div class="form-header form-header-bordered">
        <div class="form-header-top">
            <h1 class="form-header-title">@lang('Settings')</h1>
        </div>
        <div class="form-header-toolbar">
            <button class="btn btn-sm btn-primary" type="submit">{{ __('Save') }}</button>
            @if (config('responsecache.enabled'))
                <a class="btn btn-sm btn-light me-2" href="{{ route('admin::clear-cache') }}">
                    {{ __('Clear cache') }}
                </a>
            @endif
        </div>
    </div>

    <div class="form-body">

        <div class="mb-3">
            <label class="form-label">{{ __('Website title') }}</label>
            @foreach (locales() as $locale)
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">{{ strtoupper($locale) }}</span>
                        <input class="form-control" type="text" name="{{ $locale }}[website_title]" value="{{ $data->$locale->website_title ?? '' }}" />
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Website description') }}</label>
            @foreach (locales() as $locale)
                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text">{{ strtoupper($locale) }}</span>
                        <textarea class="form-control" name="{{ $locale }}[website_description]" rows="3" maxlength="400">{{ $data->$locale->website_description ?? '' }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <label class="form-label">{{ __('Publish website') }}</label>

        <div class="mb-3">
            @foreach (locales() as $locale)
                <div class="form-check form-check-inline">
                    <input type="hidden" name="{{ $locale }}[status]" value="0" />
                    <input class="form-check-input" type="checkbox" name="{{ $locale }}[status]" id="{{ $locale }}[status]" value="1" @if (isset($data->$locale) and $data->$locale->status) checked @endif />
                    <label class="form-check-label" for="{{ $locale }}[status]">{{ strtoupper($locale) }}</label>
                </div>
            @endforeach
        </div>

        <label class="form-label">{{ __('Website baseline') }}</label>
        @foreach (locales() as $locale)
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">{{ strtoupper($locale) }}</span>
                    <input class="form-control" type="text" name="{{ $locale }}[website_baseline]" value="{{ $data->$locale->website_baseline ?? '' }}" />
                </div>
            </div>
        @endforeach

        @if (!config('typicms.welcome_message_url'))
            {!! BootForm::textarea(__('Administration Welcome Message'), 'welcome_message')->rows(3) !!}
        @endif

        {!! BootForm::hidden('auth_public')->value(0) !!}
        {!! BootForm::hidden('register')->value(0) !!}

        <div class="row">
            <div class="col-lg-12">
                <h2 class="my-3">@lang('Contact')</h2>
                {!! BootForm::text(__('Name'), 'contact_name') !!}
                {!! BootForm::text(__('Phone'), 'contact_phone') !!}
                {!! BootForm::text(__('Address'), 'contact_address') !!}
                {!! BootForm::text(__('Email'), 'contact_email') !!}
            </div>
        </div>
    </div>

    {!! BootForm::close() !!}
</x-core::layouts.admin>
