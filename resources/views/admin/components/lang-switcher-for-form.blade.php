@props(['locales' => locales()])

@if (count($locales) > 1)
    <div class="btn-group btn-group-sm ms-auto">
        <button class="btn-solid theme-secondary" type="button" id="dropdownLangSwitcher" data-bs-toggle="menu" data-bs-placement="bottom-end" aria-haspopup="true" aria-expanded="false">
            <span id="active-locale">{{ config('typicms.content_locale') ? __('languages.' . config('typicms.content_locale')) : __('All languages') }}</span>
        </button>
        <div class="menu" aria-labelledby="dropdownLangSwitcher">
            @foreach ($locales as $locale)
                <a class="menu-item btn-lang-js @if (!session('allLocalesInForm') && $locale === (string) config('typicms.content_locale')) active @endif" href="#" data-locale="{{ $locale }}">
                    {{ __('languages.' . $locale) }}
                </a>
            @endforeach

            <div class="menu-divider"></div>
            <a class="menu-item btn-lang-js @if (session('allLocalesInForm')) active @endif" href="#" data-locale="all">{{ __('All languages') }}</a>
        </div>
    </div>
@endif
