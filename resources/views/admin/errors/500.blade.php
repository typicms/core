<x-core::layouts.admin title="Error 500" body-class="error-500">
    <div class="error">
        <div class="error-header error-header-bordered">
            <h1 class="error-title">{{ __('Error :code', ['code' => '500']) }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('Sorry, a server error occurred.') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
