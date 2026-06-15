<x-core::layouts.admin :title="__('Page Not Found')" body-class="error-404">
    <div class="error">
        <div class="error-header error-header-bordered">
            <h1 class="error-title">{{ __('Page Not Found') }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('The page you’re looking for doesn’t exist or has been moved.') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
