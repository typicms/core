<x-core::layouts.admin :title="__('Access denied')" body-class="error-403">
    <div class="error">
        <div class="error-header error-header-bordered">
            <h1 class="error-title">{{ __('Access denied') }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('You don’t have permission to access this page.') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
