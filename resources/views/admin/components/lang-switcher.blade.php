@props(['page' => null, 'model' => null])

@php $lang = app()->getLocale(); @endphp

@if ($enabledLocales = enabledLocales() and count($enabledLocales) > 1)
    <nav class="lang-switcher dropdown">
        <button class="lang-switcher-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="dropdownLangSwitcher">
            {{ $lang }}
        </button>
        <ul class="lang-switcher-list dropdown-menu" aria-labelledby="dropdownLangSwitcher">
            @foreach ($enabledLocales as $locale)
                @if ($locale !== $lang)
                    @php
                        $url = url('/' . $locale);
                        if ($page && $page->isPublished($locale)) {
                            $url = $model && $model->isPublished($locale) ? $model->url($locale) ?? $page->url($locale) : $page->url($locale);
                        }
                    @endphp
                    <li>
                        <a class="lang-switcher-item" href="{{ $url }}" hreflang="{{ $locale }}">
                            <abbr lang="{{ $locale }}" title="@lang('languages.' . $locale, [], $locale)">{{ $locale }}</abbr>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
@endif
