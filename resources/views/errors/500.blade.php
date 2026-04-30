<x-core::layouts.public
    :title="__('Error :code', ['code' => '500']) . ' – ' . websiteTitle()"
    body-class="error-500"
>
    <x-slot:lang-switcher></x-slot:lang-switcher>

    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">@lang('Server Error')</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>
                @lang('Sorry, a server error occurred.')
                <br>
                @lang('Error :code', ['code' => '500'])
            </p>
        </div>
    </div>
</x-core::layouts.public>
