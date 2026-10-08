@props(['locales' => locales(), 'model', 'langSwitcher' => true, 'preview' => true])

<div class="header-toolbar">
    <button class="btn-sm btn-solid theme-primary" value="true" id="exit" name="exit" type="submit">{{ __('Save and exit') }}</button>
    <button class="btn-sm btn-solid theme-secondary" type="submit">{{ __('Save') }}</button>
    @if ($preview && method_exists($model, 'url') && method_exists($model, 'previewUrl'))
        @foreach ($locales as $locale)
            <a class="btn-sm btn-solid theme-secondary btn-preview" href="{{ $model->previewUrl($locale) }}" data-language="{{ $locale }}">{{ __('Preview') }}</a>
        @endforeach
    @endif

    {{ $slot }}
    @if ($langSwitcher)
        <x-core::lang-switcher-for-form />
    @endif
</div>
