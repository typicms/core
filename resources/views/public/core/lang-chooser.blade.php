<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ websiteTitle() }} Choose language</title>
    <meta property="og:site_name" content="{{ websiteTitle() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ URL::full() }}" />
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}" />
    @vite(['resources/scss/public.scss'])
    <x-core::feed-links />
</head>

<body class="lang-chooser">
    <div class="page-header lang-chooser-header">
        <h1 class="lang-chooser-title">Choose language</h1>
    </div>

    <ul class="lang-chooser-list">
        @foreach (enabledLocales() as $locale)
            <li class="lang-chooser-list-item">
                <a class="lang-chooser-list-anchor" href="{{ $homepage->url($locale) }}">{{ __('languages.' . $locale, [], $locale) }}</a>
            </li>
        @endforeach
    </ul>
</body>
