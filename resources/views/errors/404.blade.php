<x-core::layouts.public :title="__('Page Not Found') . ' – ' . websiteTitle()" body-class="error-404">
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">{{ __('Page Not Found') }}</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>{{ __('The page you’re looking for doesn’t exist or has been moved.') }}</p>
            <p>
                <a class="btn btn-primary" href="{{ homeUrl() }}">{{ __('Back to homepage') }}</a>
            </p>
        </div>
    </div>
</x-core::layouts.public>
