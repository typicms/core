<x-core::layouts.public :title="__('Error :code', ['code' => '403']) . ' – ' . websiteTitle()" body-class="error-403">
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">{{ __('Error :code', ['code' => '403']) }}</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>
                {{ __('Sorry, you are not authorized to view this page.') }}
                <br />
                {!! trans('Go to our homepage?', ['a_open' => '<a href="/">', 'a_close' => '</a>']) !!}
            </p>
        </div>
    </div>
</x-core::layouts.public>
