<x-core::layouts.public
    :title="__('Error :code', ['code' => '401']) . ' – ' . websiteTitle()"
    body-class="error-401"
>
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">
                @lang('Error :code', ['code' => '401'])
            </h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>
                @lang('Unauthorized')
                <br>
                @lang('Go to our homepage?', ['a_open' => '<a href="/">', 'a_close' => '</a>'])
            </p>
        </div>
    </div>
</x-core::layouts.public>
