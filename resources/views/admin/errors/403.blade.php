<x-core::layouts.admin title="Error 403" body-class="error-403">
    <div class="error">
        <div class="error-header error-header-bordered">
            <h1 class="error-title">{{ __('Error :code', ['code' => '403']) }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('Sorry, you are not authorized to view this page.') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
