<x-core::layouts.admin :title="__('Something went wrong')" body-class="error-500">
    <div class="error">
        <div class="error-header error-header-bordered">
            <h1 class="error-title">{{ __('Something went wrong') }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('We hit an unexpected error on our end. Please try again in a moment.') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
