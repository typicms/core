@props(['page' => null, 'model' => null])

@can('see navbar')
    <nav class="typicms-navbar navbar navbar-expand justify-content-between sticky-top">
        <div class="container-fluid">
            <button
                class="btn-link lg:d-none px-1"
                type="button"
                data-bs-toggle="drawer"
                data-bs-target="#offcanvasResponsive"
                aria-controls="offcanvasResponsive"
                aria-label="{{ __('Toggle navigation') }}"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="typicms-navbar-brand navbar-brand" href="{{ route('admin::dashboard') }}">
                {{-- <x-core::logo class="typicms-navbar-brand-logo" /> --}}
                {{ websiteTitle() }}
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <x-core::navbar-public-link :$page :$model />
                </li>
                <li class="nav-item">
                    <button class="nav-link" type="button" data-bs-toggle="menu" data-bs-placement="bottom-end" aria-expanded="false">
                        <span class="icon-circle-user-round me-1"></span>
                        <span class="d-none lg:d-inline">{{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}</span>
                    </button>
                    <ul class="menu">
                        <li>
                            <h6 class="menu-header">{{ auth()->user()->email }}</h6>
                        </li>
                        @can('edit profile')
                            <li>
                                <a class="menu-item" href="{{ route('admin::profile') }}">{{ __('Profile') }}</a>
                            </li>
                        @endcan
                        <li>
                            <form action="{{ route(mainLocale() . '::logout') }}" method="post">
                                {{ csrf_field() }}
                                <button class="menu-item" type="submit">{{ __('Logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </li>
                @can('read settings')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin::index-settings') }}">
                            <span class="icon-settings me-1"></span>
                            <span class="d-none lg:d-inline">{{ __('Settings') }}</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </nav>
@endcan
