@props(['model' => null])
@php
    $contentLocale = (string) config('typicms.content_locale');
    if (isset($model) && $model->id && $contentLocale) {
        $locale = isLocaleEnabled($contentLocale) ? $contentLocale : app()->getLocale();
        $url = method_exists($model, 'url')
            ? $model->url($locale) ?? url('/')
            : url('/');
    } elseif (($module = Request::segment(2)) and Route::has($contentLocale . '::index-' . $module)) {
        $url = route($contentLocale . '::index-' . $module);
    } else {
        $url = url('/');
    }
@endphp

<a class="nav-link" href="{{ $url }}">
    <span class="icon-eye me-1"></span>
    <span class="d-none d-lg-inline">{{ __('View website', [], config('typicms.navbar_locale')) }}</span>
</a>
