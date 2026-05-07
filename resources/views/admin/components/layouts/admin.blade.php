@props([
    'title' => '',
    'bodyClass' => '',
    'page' => null,
    'model' => null
])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="auto">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="api-token" content="{{ auth()->user()->api_token ?? '' }}" />
    <meta name="public-css-url" content="{{ Vite::asset('resources/scss/public.scss') }}" />
    <title>[admin] {{ $title }} – {{ config('typicms.' . app()->getLocale() . '.website_title') }}</title>
    <script src="{{ Vite::asset('resources/js/admin/theme-switcher.ts') }}"></script>
    @stack('css')
    @vite('resources/scss/admin.scss')
</head>

<body
    @class([
        'has-navbar' => auth()->user()?->can('see navbar'),
        $bodyClass => filled($bodyClass)
    ])
>
    <x-core::navbar :$page :$model />

    <div id="app" class="main">
        @isset($sidebar)
            {{ $sidebar }}
        @else
            @include('admin::core._sidebar')
        @endisset
        <div class="content">{{ $slot }}</div>
    </div>

    @include('admin::core._javascript')

    @vite('resources/js/admin.js')

    @stack('js')

    <script type="module">
        alertify.logPosition('bottom right');
        @if (session('message'))
        alertify.success('{{ session('message') }}');
        @endif
        @if (session('success'))
        alertify.success('{{ session('success') }}');
        @endif
        @if (session('error'))
        alertify.error('{{ session('error') }}');
        @endif
    </script>
</body>
</html>
