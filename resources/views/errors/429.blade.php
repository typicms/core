<x-core::layouts.public :title="__('Too Many Requests') . ' – ' . websiteTitle()" body-class="error-429">
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">{{ __('Too Many Requests') }}</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>{{ __('Too Many Requests') }}</p>
            <p>
                <a class="btn btn-primary" href="{{ homeUrl() }}">{{ __('Back to homepage') }}</a>
            </p>
        </div>
    </div>
</x-core::layouts.public>
