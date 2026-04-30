<x-core::layouts.public
    :title="__('Error :code', ['code' => '419']) . ' – ' . websiteTitle()"
    body-class="error-419"
>
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">
                @lang('Error :code', ['code' => '419'])
            </h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>
                @lang('Page Expired')
                <br>
                @lang('Go to our homepage?', ['a_open' => '<a href="/">', 'a_close' => '</a>'])
            </p>
        </div>
    </div>
</x-core::layouts.public>
