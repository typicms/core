<x-core::layouts.admin title="Error 404" body-class="error-404">
    <div class="error">
        <div class="error-header error-header-bordered">
            <h1 class="error-title">{{ __('Error :code', ['code' => '404']) }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('Sorry, this page was not found.') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
