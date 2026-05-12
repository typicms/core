<x-core::layouts.public :title="__('Error :code', ['code' => '404']) . ' – ' . websiteTitle()" body-class="error-404">
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">{{ __('Error :code', ['code' => '404']) }}</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>
                {{ __('Sorry, this page was not found.') }}
                <br />
                {!! __('Go to our homepage?', ['a_open' => '<a href="/">', 'a_close' => '</a>']) !!}
            </p>
        </div>
    </div>
</x-core::layouts.public>
