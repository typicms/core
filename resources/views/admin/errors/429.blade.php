<x-core::layouts.admin title="Error 429" body-class="error-429">
    <div class="error">
        <div class="error-header">
            <h1 class="error-title">{{ __('Error :code', ['code' => '429']) }}</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">{{ __('Too Many Requests') }}</p>
        </div>
    </div>
</x-core::layouts.admin>
