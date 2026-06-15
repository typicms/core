<x-core::layouts.public :title="__('Access denied') . ' – ' . websiteTitle()" body-class="error-403">
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">{{ __('Access denied') }}</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>{{ __('You don’t have permission to access this page.') }}</p>
            <p>
                <a class="btn btn-primary" href="{{ homeUrl() }}">{{ __('Back to homepage') }}</a>
            </p>
        </div>
    </div>
</x-core::layouts.public>
