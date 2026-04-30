@props([
    'title' => '',
    'bodyClass' => 'auth-background',
    'mainClass' => '',
])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="auto">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="api-token" content="{{ auth()->user()->api_token ?? '' }}" />
    <title>{{ $title }} – {{ config('typicms.' . app()->getLocale() . '.website_title') }}</title>
    <script src="{{ Vite::asset('resources/js/admin/theme-switcher.ts') }}"></script>
    @stack('css')
    @vite('resources/scss/admin.scss')
</head>

<body @class([$bodyClass => filled($bodyClass)])>
    <div id="app" @class([$mainClass => filled($mainClass)])>
        {{ $slot }}
    </div>

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
