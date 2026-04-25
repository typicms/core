@props(['show' => showAdminButtons()])

@if ($show)
    <form action="{{ route(mainLocale() . '::logout') }}" method="post">
        @csrf
        <button class="admin-buttons-logout" type="submit" title="{{ __('Logout') }}">
            <span class="icon-log-out"></span>
            <span class="visually-hidden">{{ __('Logout') }}</span>
        </button>
    </form>
@endif
