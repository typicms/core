@props([
    'page' => null,
    'model' => null,
])

@php
    $target = $model ?? $page;
@endphp

@if ($target)
    @foreach (enabledLocales() as $locale)
        @php
            $url = $target->url($locale);
        @endphp

        @if ($url)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}" />
        @endif
    @endforeach

    @php
        $defaultUrl = $target->url(mainLocale());
    @endphp

    @if ($defaultUrl)
        <link rel="alternate" hreflang="x-default" href="{{ $defaultUrl }}" />
    @endif
@endif
