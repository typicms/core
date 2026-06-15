<x-core::layouts.public :title="__('Unauthorized') . ' – ' . websiteTitle()" body-class="error-401">
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">{{ __('Unauthorized') }}</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>{{ __('Unauthorized') }}</p>
            <p>
                <a class="btn btn-primary" href="{{ homeUrl() }}">{{ __('Back to homepage') }}</a>
            </p>
        </div>
    </div>
</x-core::layouts.public>
