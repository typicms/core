@props(['locales' => locales(), 'model', 'langSwitcher' => true, 'preview' => true])

<div class="header-toolbar">
    <button class="btn btn-sm btn-primary" value="true" id="exit" name="exit" type="submit">
        @lang('Save and exit')
    </button>
    <button class="btn btn-sm btn-light" type="submit">
        @lang('Save')
    </button>
    @if ($preview && method_exists($model, 'url') && method_exists($model, 'previewUrl'))
        @foreach ($locales as $locale)
            <a class="btn btn-sm btn-light btn-preview" href="{{ $model->previewUrl($locale) }}" data-language="{{ $locale }}">
                @lang('Preview')
            </a>
        @endforeach
    @endif

    {{ $slot }}
    @if ($langSwitcher)
        <x-core::lang-switcher-for-form />
    @endif
</div>
