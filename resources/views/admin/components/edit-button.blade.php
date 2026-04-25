@props([
    'url' => null,
    'page' => null,
    'model' => null,
    'logout' => false,
    'show' => auth('web')->user()?->can('see navbar') && !request()->boolean('preview') && !request()->is('*/create-passkey'),
])

@php
    if ($url === null) {
        $url = match (true) {
            $model !== null => $model->editUrl(),
            $page !== null && $page->module && Route::has('admin::index-' . $page->module) => route('admin::index-' . $page->module),
            $page !== null => $page->editUrl(),
            default => route('admin::dashboard'),
        };
        $url .= '?locale=' . app()->getLocale();
    }
@endphp

@if ($show)
    <div class="admin-buttons">
        <div class="admin-buttons-container">
            @if ($logout)
                <form action="{{ route(mainLocale() . '::logout') }}" method="post">
                    @csrf
                    <button class="admin-buttons-edit" type="submit" title="{{ __('Logout') }}">
                        <span class="icon-log-out"></span>
                        <span class="visually-hidden">{{ __('Logout') }}</span>
                    </button>
                </form>
            @endif
            @if ($url)
                <a class="admin-buttons-edit" href="{{ $url }}" title="{{ __('Edit') }}">
                    <span class="icon-pencil"></span>
                    <span class="visually-hidden">{{ __('Edit') }}</span>
                </a>
            @endif
        </div>
    </div>
@endif
