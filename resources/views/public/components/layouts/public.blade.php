@props([
    'title' => '',
    'description' => '',
    'keywords' => '',
    'ogTitle' => '',
    'ogImage' => '',
    'canonical' => null,
    'bodyClass' => '',
    'page' => null,
    'model' => null,
])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}" />
    <meta name="keywords" content="{{ $keywords }}" />

    <meta property="og:site_name" content="{{ websiteTitle() }}" />
    <meta property="og:title" content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $description }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ URL::full() }}" />
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta name="twitter:card" content="summary_large_image" />

    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    @vite(['resources/scss/public.scss', 'resources/js/public.js'])

    <x-core::feed-links />

    @stack('css')
</head>

<body @class([
    'body-' . app()->getLocale(),
    $bodyClass => filled($bodyClass),
]) id="top">

    @isset($skipLinks)
        {{ $skipLinks }}
    @else
        <div class="skip-to-content">
            <a href="#main" class="skip-to-content-link">@lang('Skip to content')</a>
        </div>
    @endisset

    <x-core::logout-button />

    @auth
        @if (auth()->user()->isImpersonating())
            <a class="stop-impersonation-button" href="{{ route(app()->getLocale() . '::stop-impersonation') }}">
                @lang('Stop impersonation')
            </a>
        @endif
    @endauth

    <div class="site-container">
        @isset($header)
            {{ $header }}
        @else
            <header class="header" id="header">
                <div class="header-container">
                    @isset($headerTitle)
                        {{ $headerTitle }}
                    @else
                        <div class="header-title"><x-core::header-title /></div>
                    @endisset
                    <div class="header-offcanvas" id="navigation">
                        <button class="hamburger" type="button" id="menu-button" data-bs-toggle="collapse" data-bs-target="#navigation-container" aria-expanded="false" aria-controls="navigation-container">
                            <span class="visually-hidden">@lang('Menu')</span>
                        </button>
                        <div class="navigation collapse fade" id="navigation-container" data-bs-parent="#navigation">
                            <nav class="primary-nav" aria-label="@lang('Primary navigation')">
                                @menu('primary')
                            </nav>
                            @include('public::search._form')
                            @isset($langSwitcher)
                                {{ $langSwitcher }}
                            @else
                                <x-core::lang-switcher :page="$page" :model="$model" />
                            @endisset
                        </div>
                    </div>
                </div>
            </header>
        @endisset

        <main class="main" id="main">
            <div class="container">
                <x-core::edit-button :page="$page" :model="$model" />
            </div>
            {{ $slot }}
        </main>

        @isset($footer)
            {{ $footer }}
        @else
            <footer class="footer">
                <div class="footer-container">
                    <nav class="social-nav" aria-label="@lang('Social links')">
                        @menu('social')
                    </nav>
                    <nav class="footer-nav" aria-label="@lang('Footer navigation')">
                        @menu('footer')
                    </nav>
                    <nav class="legal-nav" aria-label="@lang('Legal links')">
                        @menu('legal')
                    </nav>
                </div>
            </footer>
        @endisset

        <div class="anchor-top disabled" id="anchor-top" role="complementary">
            <a class="anchor-top-button" href="#top" aria-label="@lang('Back to top')">
                <span class="icon-arrow-up"></span>
            </a>
        </div>

    </div>

    @can('see unpublished items')
        @if (request()->boolean('preview'))
            <script src="{{ asset('js/previewmode.js') }}"></script>
        @endif
    @endcan

    @stack('js')
</body>

</html>
