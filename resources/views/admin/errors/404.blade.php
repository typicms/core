<x-core::layouts.admin title="Error 404" body-class="error-404">
    <div class="error">
        <div class="error-header">
            <h1 class="error-title">@lang('Error :code', ['code' => '404'])</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">
                @lang('Sorry, this page was not found.')
            </p>
        </div>
    </div>
</x-core::layouts.admin>
