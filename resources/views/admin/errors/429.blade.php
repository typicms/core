<x-core::layouts.admin :title="__('Too Many Requests')" body-class="error-429">
    <div class="error">
        <div class="error-header error-header-bordered">
            <h1 class="error-title">{{ __('Too Many Requests') }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('Too Many Requests') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
